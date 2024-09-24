<?php
declare(strict_types=1);

namespace App\Repository\Interface;

use Doctrine\ORM\Tools\Pagination\Paginator;

interface PaginatedRepositoryInterface
{
    /**
     * @param array|null $orderBy
     * @param array $searchFields
     * @param int $page
     * @param int $limit
     */
    public function getPaginatedResults(
        ?array $orderBy = null,
        array $searchFields = [],
        int $page = 0,
        int $limit = 30,
    ): Paginator;
    public function getPaginatedResultCount(
        ?array $orderBy = null,
        array $searchFields = [],
    ): int;
}