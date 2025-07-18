<?php
declare(strict_types=1);

namespace App\Twig\Components\Trait;

use App\Repository\Interface\PaginatedRepositoryInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use UnhandledMatchError;

/**
 * @template T
 */
trait PaginatedRepositoryTrait
{
    use PaginatedTrait;

    /** @var PaginatedRepositoryInterface<T>  */
    private PaginatedRepositoryInterface $repository;

    /** @var array<string, 'ASC'|'DESC'> */
    private array $paginatedOrderBy = [];

    /** @var array<string, mixed> */
    private array $paginatedSearchFields = [];

    /**
     * @param array<string, 'ASC'|'DESC'> $paginatedOrderBy
     * @return $this
     */
    public function setPaginatedOrderBy(array $paginatedOrderBy): static
    {
        $this->paginatedOrderBy = $paginatedOrderBy;
        return $this;
    }

    /**
     * @param array<string, mixed> $paginatedSearchFields
     * @return $this
     */
    public function setPaginatedSearchFields(array $paginatedSearchFields): static
    {
        $this->paginatedSearchFields = $paginatedSearchFields;
        return $this;
    }

    /**
     * @param PaginatedRepositoryInterface<T> $repository
     * @return void
     */
    private function setRepository(PaginatedRepositoryInterface $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * @param array<string, mixed> ...$args
     * @return int|null
     * @throws Exception
     */
    public function getNumberOfRows(mixed ... $args): ?int
    {
        if ($this->numberOfRows === null) {
            $arguments = [
                "orderBy" => $this->paginatedOrderBy,
                "searchFields" => $this->paginatedSearchFields,
                "page" => $this->page,
                "limit" => $this->limit,
                ... $args,
            ];

            $numberOfRows = $this->getPaginatedResults(... $arguments)->count();
            $this->setNumberOfRows($numberOfRows);
        }

        return $this->numberOfRows;
    }

    /**
     * @param mixed ...$args
     * @return Paginator<T>
     * @throws Exception
     */
    private function getPaginatedResults(mixed ... $args): Paginator
    {
        try {
            $arguments = [
                "orderBy" => $this->paginatedOrderBy,
                "searchFields" => $this->paginatedSearchFields,
                "page" => $this->page,
                "limit" => $this->limit,
                ... $args,
            ];

            $paginatedDesigns = $this->repository->getPaginatedResults(... $arguments);
        } catch (UnhandledMatchError) {
            throw new Exception("An error occured during the query.");
        }

        return $paginatedDesigns;
    }
}
