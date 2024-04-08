<?php
declare(strict_types=1);

namespace App\Repository\Traits;

use Closure;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

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

    private function addExpressionsToSearchQuery(QueryBuilder $queryBuilder, array $expressions): QueryBuilder
    {
        return match (count($expressions)) {
            0 => $queryBuilder,
            1 => $queryBuilder->andWhere($expressions[0]),
            default => $queryBuilder->andWhere($queryBuilder->expr()->andX(...$expressions)),
        };
    }

    private function addExpressionsToHavingQuery(QueryBuilder $queryBuilder, array $expressions): QueryBuilder
    {
        return match(count($expressions)) {
            0 => $queryBuilder,
            1 => $queryBuilder->andHaving($expressions[0]),
            default => $queryBuilder->andHaving($queryBuilder->expr()->andX(...$expressions))
        };
    }

    /**
     * @param array $searchFields
     * @param Closure $match
     * @return array
     */
    private function createExpressions(array $searchFields, Closure $match): array
    {
        $expressions = [];
        foreach ($searchFields as $searchField => $searchValue) {
            if ($searchValue === null or (is_string($searchValue) and strlen($searchValue) === 0)) {
                continue;
            }

            $expression = $match($searchField ,$searchValue);

            if ($expression !== null) {
                $expressions[] = $expression;
            }
        }

        return $expressions;
    }
}