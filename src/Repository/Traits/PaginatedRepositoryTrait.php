<?php
declare(strict_types=1);

namespace App\Repository\Traits;

use Closure;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Hoa\Stream\Composite;

trait PaginatedRepositoryTrait
{
    public function getPaginatedResults(
        ?array $orderBy = null,
        array $searchFields = [],
        int $page = 0,
        int $limit = 30,
    ): Paginator {
        $queryBuilder = $this->getPaginatedQuery();

        if ($orderBy !== null) {
            $queryBuilder = $this->addOrderBy($queryBuilder, $orderBy);
        }

        if (!empty($searchFields)) {
            $queryBuilder = $this->addSearchFields($queryBuilder, $searchFields);
        }

        $queryBuilder = $queryBuilder
            ->setFirstResult($limit * $page)
            ->setMaxResults($limit)
        ;

        return new Paginator($queryBuilder, fetchJoinCollection: true);
    }

    public function getPaginatedResultCount(
        ?array $orderBy = null,
        array $searchFields = [],
    ): int {
        $queryBuilder = $this->getPaginatedCountQuery();

        if (!empty($searchFields)) {
            $queryBuilder = $this->addSearchFields($queryBuilder, $searchFields);
        }

        return (new Paginator($queryBuilder, fetchJoinCollection: true))->count();
    }

    abstract private function getPaginatedQuery(): QueryBuilder;
    abstract private function getPaginatedCountQuery(): QueryBuilder;
    abstract private function addOrderBy(QueryBuilder $queryBuilder, array $orderBy): QueryBuilder;
    abstract private function addSearchFields(QueryBuilder $queryBuilder, array $searchFields): QueryBuilder;
}