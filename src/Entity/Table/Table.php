<?php
declare(strict_types=1);

namespace App\Entity\Table;

use Traversable;

class Table
{
    private $isActive = null;

    public function __construct(
        private ?iterable $data = null,
        /** @var Column[] */
        private array $columns = [],
        private int $sortColumn = 1,
        private ?int $maxRows = null,
        ?callable $isActive = null,
    ) {
        $this->isActive = $isActive;
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

    public function toArray()
    {
        $table = [
            "numberOfRows" => 0,
            "maxNumberOfRows" => $this->maxRows,
            "rows" => [],
            "columns" => [],
        ];

        $isActive = $this->isActive;

        foreach ($this->columns as $column) {
            $table["columns"][] = [
                "label" => $column->getTitle(),
                "type" => $column::class,
                "showLabel" => $column::renderTitle,
                "widthRecommendation" => $column->getWidthRecommendation(),
            ];
        }

        foreach ($this->data as $datum) {
            $row = [];

            foreach ($this->columns as $column) {
                $row[] = [
                    "value" => $column->getRender($datum),
                    "raw" => $column::raw,
                    "component" => $column::component,
                    "isActive" => $isActive ? $isActive($datum) : false,
                ];
            }

            $table["rows"][] = $row;
            $table["numberOfRows"]++;
        }

        return $table;
    }
}