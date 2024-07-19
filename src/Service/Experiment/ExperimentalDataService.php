<?php
declare(strict_types=1);

namespace App\Service\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDatum;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunDataSet;
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
use Doctrine\ORM\Query;
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
        if (method_exists($design->getFields(), "isInitialized") && $design->getFields()->isInitialized() === false) {
            $fields = $design->getFields()->matching((new Criteria())->where(new Comparison("exposed", "=", "true")));
        } else {
            $fields = $design->getFields()->filter(fn (ExperimentalDesignField $field) => $field->isExposed());
        }

        return $fields;
    }

    private function getResults(?array $orderBy = null, array $searchFields = [], ExperimentalDesign $design = null): QueryBuilder
    {
        $queryBuilder = $this->getBaseQuery($design);

        if (!empty($searchFields)) {
            $queryBuilder = $this->addSearchFields($queryBuilder, $searchFields, $design);
        }

        if (empty($orderBy)) {
            $queryBuilder->addOrderBy("exr.createdAt", "DESC");
        }

        return $queryBuilder;
    }

    public function getPaginatedResults(?array $orderBy = null, array $searchFields = [], int $page = 0, int $limit = 30, ExperimentalDesign $design = null): array
    {
        if ($design === null) {
            throw new \Exception("You must give an experimental design.");
        }

        // Retrieve (paginated) conditions to show in the table.
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
        return $this->createDataArray($paginatedConditions, $entities, $design);
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

        $pushEntity = function(Collection $data, &$entitiesToFetch) use ($design) {
            foreach ($data as $datum) {
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

                    // Nothing to retrieve if the field is not exposed
                    if (!$field->isExposed()) {
                        return;
                    }

                    $configuredEntityTypes = explode("|", $field->getFormRow()->getConfiguration()["entityType"] ?? "");

                    // Single class
                    if (count($configuredEntityTypes) <= 1) {
                        $entitiesToFetch[$class][$id->toRfc4122()] = true;
                    } else {
                        $entitiesToFetch[$class][$id->toRfc4122()] = $configuredEntityTypes[1];
                    }
                }
            }
        };

        /** @var ExperimentalRunCondition $condition */
        foreach ($conditions as $condition) {
            // Top values
            $pushEntity($condition->getExperimentalRun()->getData(), $entitiesToFetch);

            // Condition values
            $pushEntity($condition->getData(), $entitiesToFetch);

            // Datasets
            foreach ($condition->getExperimentalRun()->getDataSets()->filter(fn (ExperimentalRunDataSet $set) => $set->getCondition() === $condition) as $dataSet) {
                $pushEntity($dataSet->getData(), $entitiesToFetch);
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
                $entityClasses = [];

                foreach ($entities as $id => $entityClass) {
                    if (!array_key_exists($entityClass, $entityClasses)) {
                        $entityClasses[$entityClass] = [];
                    }

                    $entityClasses[$entityClass][] = $id;
                }

                foreach ($entityClasses as $entityClass => $listOfIds) {
                    $entities = $entityRepository->getLotsWithSubstance($entityClass, $listOfIds);

                    foreach ($entities as $entity) {
                        $entitiesToFetch[$class][$entity->getLot()->getId()->toRfc4122()] = $entity;
                    }
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
    public function createDataArray(Paginator $conditions, array $entitiesToFetch, ExperimentalDesign $design): array
    {
        $data = [];

        $pushColumn = function (Collection $data, &$row) use ($entitiesToFetch, $design) {
            /** @var ExperimentalDatum $datum */
            foreach ($data as $datum) {
                $field = $design->getFields()->filter(fn (ExperimentalDesignField $field) => $field->isExposed() && $field->getFormRow()->getFieldName() === $datum->getName())->first();

                if ($field === false) {
                    continue;
                }

                $key = $datum->getName();

                if ($datum->getType() === DatumEnum::EntityReference) {
                    /** @var Uuid $id */
                    [$id, $class] = $datum->getValue();

                    $value = $entitiesToFetch[$class][$id->toRfc4122()];
                } else {
                    $value = $datum->getValue();
                }

                if (array_key_exists($key, $row)) {
                    if (!is_array($row[$key])) {
                        $row[$key] = [$row[$key]];
                    }

                    $row[$key][] = $value;
                } else {
                    $row[$key] = $value;
                }
            }
        };

        foreach ($conditions as $condition) {
            $row = [
                "set" => $condition,
                "run" => $condition->getExperimentalRun(),
            ];

            $pushColumn($condition->getExperimentalRun()->getData(), $row);
            $pushColumn($condition->getData(), $row);

            $row["data"] = [];

            $maxRows = 10;

            foreach ($condition->getExperimentalRun()->getDataSets() as $dataSet) {
                $subRow = [];
                $pushColumn($dataSet->getData(), $subRow);
                $row["data"][] = $subRow;

                $row["data"] = array_unique($row["data"], SORT_REGULAR);

                if (count($row["data"]) == $maxRows) {
                    break;
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
                $this->getSearchQueryBuilderForFieldType($fieldRow->getRole(), $abbreviation_suffix, $nameParamName)
                    ->andWhere("data$abbreviation_suffix.referenceUuid IN (:$referencesParamName)")
                    ->getDQL(),
            );
        } elseif ($fieldRow->getFormRow()->getType() === FormRowTypeEnum::TextType) {
            $queryBuilder->setParameter($nameParamName, $searchField);
            $queryBuilder->setParameter($referencesParamName, $this->searchService->parse($searchValue));

            return $queryBuilder->expr()->in(
                "exrc.id",
                $this->getSearchQueryBuilderForFieldType($fieldRow->getRole(), $abbreviation_suffix, $nameParamName)
                    ->andWhere("lower(convert_from(data.value, 'UTF-8')) LIKE lower(:$referencesParamName))")
                    ->getDQL(),
            );
        }

        return null;
    }

    private function getSearchQueryBuilderForFieldType(ExperimentalFieldRole $role, string $suffix, string $nameParam): QueryBuilder
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from(ExperimentalRunCondition::class, "nerc2$suffix")
            ->select("nerc2$suffix.id");

        $queryBuilder = match($role) {
            ExperimentalFieldRole::Top => $queryBuilder
                ->leftJoin("nerc2$suffix.experimentalRun", "nerc2r$suffix")
                ->leftJoin("nerc2r$suffix.data", "data$suffix"),

            ExperimentalFieldRole::Condition => $queryBuilder
                ->leftJoin("nerc2$suffix.data", "data$suffix"),

            ExperimentalFieldRole::Comparison, ExperimentalFieldRole::Datum => $queryBuilder
                ->leftJoin("nerc2$suffix.experimentalRun", "nerc2r$suffix")
                ->leftJoin("nerc2r$suffix.dataSets", "nerc2rds$suffix")
                ->leftJoin("nerc2rds$suffix.data", "data$suffix"),
        };

        $queryBuilder = $queryBuilder->where("data$suffix.name = :$nameParam");

        return $queryBuilder;
    }
}
