<?php
declare(strict_types=1);

namespace App\Service\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\Lot;
use App\Entity\SubstanceLot;
use App\Genie\Enums\DatumEnum;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Genie\Enums\FormRowTypeEnum;
use App\Repository\Experiment\ExperimentalDesignRepository;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Repository\Substance\SubstanceRepository;
use App\Repository\Traits\PaginatedRepositoryTrait;
use App\Service\Doctrine\SearchService;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Collection\CollectionInterface;
use Symfony\Component\Uid\Uuid;

class ExperimentalDataService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SearchService $searchService,
    ) {

    }

    private function getBaseQuery(ExperimentalDesign $design): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder()
            ->select("exrc")
            ->from(ExperimentalRunCondition::class, "exrc")

            ->leftJoin("exrc.experimentalRun", "exr")
            ->addSelect("exr")
            ->where("exr.design = :design")

            ->leftJoin("exrc.data", "exrcd")
            ->addSelect("exrcd")

            ->setParameter("design", $design->getId()->toRfc4122())
            ;
    }

    public function getFields(ExperimentalDesign $design): Collection
    {
        return $design->getFields()->matching((new Criteria())->where(new Comparison("role", "=", ExperimentalFieldRole::Condition)));
    }

    private function getResults(?array $orderBy = null, array $searchFields = [], ExperimentalDesign $design = null): QueryBuilder
    {
        $queryBuilder = $this->getBaseQuery($design);

        if (!empty($searchFields)) {
            $queryBuilder = $this->addSearchFields($queryBuilder, $searchFields, $design);
        }

        return $queryBuilder;
    }

    public function getPaginatedResults(?array $orderBy = null, array $searchFields = [], int $page = 0, int $limit = 30, ExperimentalDesign $design = null): array
    {
        if ($design === null) {
            throw new \Exception("You must give an experimental design.");
        }

        $queryBuilder = $this->getResults($orderBy, $searchFields, $design);
        $queryBuilder = $queryBuilder
            ->setFirstResult($limit * $page)
            ->setMaxResults($limit)
        ;

        // Retrieve rows
        /** @var Paginator<ExperimentalRunCondition> $paginatedConditions */
        $paginatedConditions = new Paginator($queryBuilder->getQuery(), fetchJoinCollection: true);

        // Prefetch entities
        $entitiesToFetch = $this->getListOfEntitiesToFetch($paginatedConditions, $design);
        $entities = $this->fetchEntitiesFromList($entitiesToFetch);

        // Create the data array
        return $this->createDataArray($paginatedConditions, $entities);
    }

    /**
     * Gets a list of entity IDs from the experimental design to fetch from the database
     *
     * @param Paginator $conditions
     * @param ExperimentalDesign $design
     * @return array{str: array{str: true|string|object}}
     */
    public function getListOfEntitiesToFetch(Paginator $conditions, ExperimentalDesign $design): array
    {
        $entitiesToFetch = [];
        $datumConfiguration = [];

        /** @var ExperimentalRunCondition $condition */
        foreach ($conditions as $condition) {
            foreach ($condition->getData() as $datum) {
                if ($datum->getType() === DatumEnum::EntityReference) {
                    /**
                     * @var Uuid $id
                     * @var string $class FQCN
                     */
                    [$id, $class] = $datum->getValue();

                    // Initialize list for that specific class if not yet done
                    if (!isset($entitiesToFetch[$class])) {
                        $entitiesToFetch[$class] = [];
                    }

                    // Check if the datum configuration has two classes
                    /** @var ExperimentalDesignField $field */
                    $field = $design->getFields()->filter(fn (ExperimentalDesignField $x) => $x->getFormRow()->getFieldName() === $datum->getName())->first();
                    $configuredEntityTypes = explode("|", $field->getFormRow()->getConfiguration()["entityType"] ?? "");

                    // Single class
                    if (count($configuredEntityTypes) <= 1) {
                        $entitiesToFetch[$class][$id->toRfc4122()] = true;
                    } else {
                        $entitiesToFetch[$class][$id->toRfc4122()] = $configuredEntityTypes[1];
                    }
                }
            }
        }

        return $entitiesToFetch;
    }

    /**
     * Fetches a list of entities by trying to get their repository and writes the fetched entities back into the original array.
     *
     *
     * @param array{str: array{str: true|string|object}} $entitiesToFetch
     * @return array{str: array{str: object}}
     */
    public function fetchEntitiesFromList(array $entitiesToFetch): array
    {
        foreach ($entitiesToFetch as $class => $entities) {
            $entityRepository = $this->entityManager->getRepository($class);

            if (current($entities) === true) {
                if (method_exists($class, "getUlid")) {
                    $idField = "ulid";
                    $idMethod = "getUlid";
                } else {
                    $idField = "id";
                    $idMethod = "getId";
                }

                $entities = $entityRepository->findBy([$idField => array_keys($entities)]);

                foreach ($entities as $entity) {
                    $entitiesToFetch[$class][$entity->$idMethod()->toRfc4122()] = $entity;
                }
            } else {
                /** @var SubstanceRepository $substanceRepository */
                #$substanceRepository = $this->entityManager->getRepository(Substance::class);
                $entities = $entityRepository->getLotsWithSubstance(current($entities), array_keys($entities));

                foreach ($entities as $entity) {
                    $entitiesToFetch[$class][$entity->getLot()->getId()->toRfc4122()] = $entity;
                }
            }
        }

        return $entitiesToFetch;
    }

    /**
     * @param Paginator<ExperimentalRunCondition> $conditions
     * @param array{str: array{str: object}} $entitiesToFetch
     * @return array{str: mixed}
     */
    public function createDataArray(Paginator $conditions, array $entitiesToFetch): array
    {
        $data = [];

        foreach ($conditions as $condition) {
            $row = [
                "set" => $condition,
                "run" => $condition->getExperimentalRun(),
            ];

            foreach ($condition->getData() as $datum) {
                if ($datum->getType() === DatumEnum::EntityReference) {
                    /** @var Uuid $id */
                    [$id, $class] = $datum->getValue();

                    $row[$datum->getName()] = $entitiesToFetch[$class][$id->toRfc4122()];
                } else {
                    $row[$datum->getName()] = $datum->getValue();
                }
            }

            $data[] = $row;
        }

        return $data;
    }

    public function getPaginatedResultCount(?array $orderBy = [], array $searchFields = [], ExperimentalDesign $design = null): int
    {
        return (new Paginator($this->getResults($orderBy, $searchFields, $design)))->count();
    }

    private function addSearchFields(QueryBuilder $queryBuilder, array $searchFields = [], ExperimentalDesign $design = null): QueryBuilder
    {
        $searchService = $this->searchService;

        $expressions = $searchService->createExpressions($searchFields, fn (string $searchField, mixed $searchValue): mixed => match($searchField) {
            "design" => $searchService->searchWithUlid($queryBuilder, "exr.design", $searchValue->getId()->toRfc4122()),
            default => $this->addVariableSearchField($queryBuilder, $searchField, $searchValue, $design)
        });

        // Remove null elements
        $expressions = array_filter($expressions, fn($x) => $x !== null);

        $queryBuilder = $searchService->addExpressionsToSearchQuery($queryBuilder, $expressions);

        return $queryBuilder;
    }

    private function addVariableSearchField(QueryBuilder $queryBuilder, string $searchField, mixed $searchValue, ExperimentalDesign $design): ?Func
    {
        /** @var ExperimentalDesignField $fieldRow */
        $fieldRow = $design->getFields()->filter(fn (ExperimentalDesignField $field) => $field->getFormRow()->getFieldName() === $searchField)->first();

        $nameParamName = "name_".$fieldRow->getFormRow()->getFieldName();
        $referencesParamName = "references_".$fieldRow->getFormRow()->getFieldName();
        $abbreviation_suffix = $fieldRow->getFormRow()->getFieldName();

        if ($fieldRow->getFormRow()->getType() === FormRowTypeEnum::EntityType) {
            $queryBuilder->setParameter($nameParamName, $searchField);
            $queryBuilder->setParameter($referencesParamName, $searchValue);

            return $queryBuilder->expr()->in(
                "exrc.id",
                $this->entityManager->createQueryBuilder()
                    ->from(ExperimentalRunCondition::class, "nerc2$abbreviation_suffix")
                    ->select("nerc2$abbreviation_suffix.id")
                    ->leftJoin("nerc2$abbreviation_suffix.data", "ned2$abbreviation_suffix")
                    ->where("ned2$abbreviation_suffix.name = :$nameParamName")
                    ->andWhere("ned2$abbreviation_suffix.referenceUuid IN (:$referencesParamName)")
                    ->getDQL()
            );
        } elseif ($fieldRow->getFormRow()->getType() === FormRowTypeEnum::TextType) {
            $queryBuilder->setParameter($nameParamName, $searchField);
            $queryBuilder->setParameter($referencesParamName, $this->searchService->parse($searchValue));

            return $queryBuilder->expr()->in(
                "exrc.id",
                $this->entityManager->createQueryBuilder()
                    ->from(ExperimentalRunCondition::class, "nerc2")
                    ->select("nerc2.id")
                    ->leftJoin("nerc2.data", "ned2")
                    ->where("ned2.name = :$nameParamName")
                    ->andWhere("lower(convert_from(ned2.value, 'UTF-8')) LIKE lower(:$referencesParamName)")
                    ->getDQL()
            );
        }

        return null;
    }
}
