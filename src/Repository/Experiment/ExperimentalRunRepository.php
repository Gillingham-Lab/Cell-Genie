<?php declare(strict_types=1);

namespace App\Repository\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Repository\Traits\PaginatedRepositoryTrait;
use App\Service\Doctrine\SearchService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use ValueError;

/**
 * @extends ServiceEntityRepository<ExperimentalRun>
 * @implements PaginatedRepositoryInterface<ExperimentalRun>
 */
class ExperimentalRunRepository extends ServiceEntityRepository implements PaginatedRepositoryInterface
{
    use PaginatedRepositoryTrait;

    public function __construct(
        ManagerRegistry $registry,
        private readonly SearchService $searchService,
    ) {
        parent::__construct($registry, ExperimentalRun::class);
    }

    /**
     * @param array<string, "ASC"|"DESC"> $orderBy
     */
    private function addOrderBy(QueryBuilder $queryBuilder, array $orderBy): QueryBuilder
    {
        foreach ($orderBy as $fieldName => $order) {
            $field = match ($fieldName) {
                "name" => "exr.name",
                "createdAt" => "exr.createdAt",
                "modifiedAt" => "expr.modifiedAt",
                default => throw new ValueError("{$fieldName} is not supported."),
            };

            $order = match ($order) {
                "DESC", "descending" => "DESC",
                default => "ASC",
            };

            $queryBuilder = $queryBuilder->addOrderBy($field, $order);
        }

        return $queryBuilder;
    }

    private function getBaseQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder("exr");

        $qb = $qb
            ->select("exr");

        return $qb;
    }

    private function getPaginatedQuery(): QueryBuilder
    {
        return $this->getBaseQuery();
    }

    private function getPaginatedCountQuery(): QueryBuilder
    {
        return $this->getBaseQuery();
    }

    /**
     * @param array<string, scalar> $searchFields
     */
    private function addSearchFields(QueryBuilder $queryBuilder, array $searchFields): QueryBuilder
    {
        $searchService = $this->searchService;

        $expressions = $searchService->createExpressions($searchFields, fn(string $searchField, mixed $searchValue): mixed => match ($searchField) {
            "design" => $searchService->searchWithUlid($queryBuilder, "exr.design", $searchValue),
            default => throw new ValueError("{$searchField} is not supported."),
        });

        $queryBuilder = $searchService->addExpressionsToSearchQuery($queryBuilder, $expressions);

        return $queryBuilder;
    }
}
