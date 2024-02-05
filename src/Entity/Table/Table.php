<?php
declare(strict_types=1);

namespace App\Entity\Table;

use Traversable;

class Table
{
    public function __construct(
        private ?iterable $data = null,
        /** @var Column[] */
        private array $columns = [],
        private int $sortColumn = 1,
    ) {

    }

    public function addColumn(Column $column): self
    {
        $this->columns[] = $column;
        return $this;
    }

    public function getColumns(): iterable
    {
        yield from $this->columns;
    }

    public function getData(): iterable
    {
        yield from $this->data;
    }

    public function setData(iterable $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getSortColumn(): int
    {
        return $this->sortColumn;
    }
}