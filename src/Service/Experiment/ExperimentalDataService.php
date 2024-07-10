<?php
declare(strict_types=1);

namespace App\Service\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Genie\Enums\DatumEnum;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Repository\Experiment\ExperimentalDesignRepository;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Repository\Traits\PaginatedRepositoryTrait;
use App\Service\Doctrine\SearchService;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityManagerInterface;
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
            $queryBuilder = $this->addSearchFields($queryBuilder, $searchFields);
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

        $paginatedConditions = new Paginator($queryBuilder->getQuery(), fetchJoinCollection: true);

        $data = [];
        $entitiesToFetch = [];
        /** @var ExperimentalRunCondition $condition */
        foreach ($paginatedConditions as $condition) {
            $row = [
                "set" => $condition,
                "run" => $condition->getExperimentalRun(),
            ];

            foreach ($condition->getData() as $datum) {
                if ($datum->getType() === DatumEnum::EntityReference) {
                    /** @var Uuid $id */
                    [$id, $class] = $datum->getValue();

                    if (!isset($entitiesToFetch[$class])) {
                        $entitiesToFetch[$class] = [];
                    }

                    $entitiesToFetch[$class][$id->toRfc4122()] = true;
                }

                $row[$datum->getName()] = $datum->getValue();
            }

            $data[] = $row;
        }

        // Fetch entities
        foreach ($entitiesToFetch as $class => $entities) {
            $entityRepository = $this->entityManager->getRepository($class);

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
        }

        // Inject entity instances back
        foreach ($data as $key => $row) {
            foreach ($row as $rowKey => $field) {
                if (is_array($field)) {
                    $entity = $entitiesToFetch[$field[1]][$field[0]->toRfc4122()] ?? null;
                    $data[$key][$rowKey] = $entity;
                }
            }
        }

        return $data;
    }

    public function getPaginatedResultCount(?array $orderBy = [], array $searchFields = [], ExperimentalDesign $design = null): int
    {
        return (new Paginator($this->getResults($orderBy, $searchFields, $design)))->count();
    }

    private function addSearchFields(QueryBuilder $queryBuilder, array $searchFields = []): QueryBuilder
    {
        $searchService = $this->searchService;

        $expressions = $searchService->createExpressions($searchFields, fn (string $searchField, mixed $searchValue): mixed => match($searchField) {
            "design" => $searchService->searchWithUlid($queryBuilder, "exr.design", $searchValue->getId()->toRfc4122()),
        });

        $queryBuilder = $searchService->addExpressionsToSearchQuery($queryBuilder, $expressions);

        return $queryBuilder;
    }
}