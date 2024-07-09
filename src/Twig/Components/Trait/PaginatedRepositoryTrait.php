<?php
declare(strict_types=1);

namespace App\Twig\Components\Trait;

use App\Repository\Interface\PaginatedRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use UnhandledMatchError;

trait PaginatedRepositoryTrait
{
    use PaginatedTrait;

    private readonly PaginatedRepositoryInterface $repository;

    private array $paginatedOrderBy = [];
    private array $paginatedSearchFields = [];

    public function setPaginatedOrderBy(array $paginatedOrderBy): static
    {
        $this->paginatedOrderBy = $paginatedOrderBy;
        return $this;
    }

    public function setPaginatedSearchFields(array $paginatedSearchFields): static
    {
        $this->paginatedSearchFields = $paginatedSearchFields;
        return $this;
    }

    private function setRepository(PaginatedRepositoryInterface $repository): void
    {
        $this->repository = $repository;
    }

    public function getNumberOfRows(): ?int
    {
        if ($this->numberOfRows === null) {
            $numberOfRows = $this->getPaginatedResults()->count();
            $this->setNumberOfRows($numberOfRows);
        }

        return $this->numberOfRows;
    }

    private function getPaginatedResults(... $args): Paginator
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
        } catch (UnhandledMatchError $e) {
            throw new Exception("An error occured during the query.");
        }

        return $paginatedDesigns;
    }
}