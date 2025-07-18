<?php
declare(strict_types=1);

namespace App\Repository\Traits;

use Doctrine\ORM\QueryBuilder;

trait HasAvailableLotSearchTrait
{
    private const string LotAvailableQuery = "SUM(CASE WHEN l.availability = 'available' THEN 1 ELSE 0 END)";

    /**
     * @param array<string, scalar> $searchFields
     */
    private function addHasAvailableLotSearch(QueryBuilder $queryBuilder, array $searchFields): QueryBuilder
    {
        $havingExpressions = $this->searchService->createExpressions($searchFields, fn(string $searchField, mixed $searchValue): mixed => match ($searchField) {
            "hasAvailableLot" => $searchValue === true ? $queryBuilder->expr()->gt($this::LotAvailableQuery, 0) : $queryBuilder->expr()->eq($this::LotAvailableQuery, 0),
            default => null,
        });

        return $this->searchService->addExpressionsToHavingQuery($queryBuilder, $havingExpressions);
    }
}
