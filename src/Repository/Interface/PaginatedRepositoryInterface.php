<?php
declare(strict_types=1);

namespace App\Repository\Interface;

use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @template TPaginated
 */
interface PaginatedRepositoryInterface
{
    /**
     * @param null|array<string, "ASC"|"DESC"> $orderBy
     * @param array<string, scalar> $searchFields
     * @param int<0, max> $page
     * @param int<1, max> $limit
     * @return Paginator<TPaginated>
     */
    public function getPaginatedResults(
        ?array $orderBy = null,
        array $searchFields = [],
        int $page = 0,
        int $limit = 30,
    ): Paginator;


    /**
     * @param null|array<string, "ASC"|"DESC"> $orderBy
     * @param array<string, scalar> $searchFields
     * @return int<0, max>
     */
    public function getPaginatedResultCount(
        ?array $orderBy = null,
        array $searchFields = [],
    ): int;
}
