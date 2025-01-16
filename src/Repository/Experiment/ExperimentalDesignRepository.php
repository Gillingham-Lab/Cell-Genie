<?php
declare(strict_types=1);

namespace App\Repository\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Repository\Interface\PaginatedRepositoryInterface;
use App\Repository\Traits\PaginatedRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use ValueError;

/**
 * @extends ServiceEntityRepository<ExperimentalDesign>
 * @implements PaginatedRepositoryInterface<ExperimentalDesign>
 */
class ExperimentalDesignRepository extends ServiceEntityRepository implements PaginatedRepositoryInterface
{
    use PaginatedRepositoryTrait;

    public function __construct(
        ManagerRegistry $registry,
    ) {
        parent::__construct($registry, ExperimentalDesign::class);
    }

    /**
     * @param array<string, "ASC"|"DESC"> $orderBy
     */
    private function addOrderBy(QueryBuilder $queryBuilder, array $orderBy): QueryBuilder
    {
        foreach ($orderBy as $fieldName => $order) {
            $field = match($fieldName) {
                "number" => "ed.number",
                default => throw new ValueError("{$fieldName} is not supported."),
            };

            $order = match($order) {
                "DESC", "descending" => "DESC",
                default => "ASC",
            };

            $queryBuilder = $queryBuilder->addOrderBy($field, $order);
        }

        return $queryBuilder;
    }

    private function getBaseQuery(): QueryBuilder
    {
        $qb = $this->createQueryBuilder("ed");

        $qb = $qb
            ->select("ed");

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
        return $queryBuilder;
    }
}