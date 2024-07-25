<?php
declare(strict_types=1);

namespace App\Entity\Table;

use Closure;
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
        private bool $spreadDatum = false,
        private ?Closure $isDisabled = null,
    ) {
        $this->isActive = $isActive;
    }

    public function addColumn(Column $column): static
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

    public function setData(iterable $data): static
    {
        $this->data = $data;
        return $this;
    }

    public function getSortColumn(): int
    {
        return $this->sortColumn;
    }

    /**
     * @return array{
     *     numberOfRows: int,
     *     maxNumberOfRows: int,
     *     columns: array{
     *         label: string,
     *         type: string,
     *         showLabel: bool,
     *         widthRecommendation: int,
     *         bold: bool,
     *     },
     *     rows: array<int, array{
     *         value: mixed,
     *         tooltip: mixed,
     *         raw: bool,
     *         component: string,
     *         isActive: bool,
     *         isDisabled: bool,
     *     }>,
     * }
     */
    public function toArray(): array
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
                "bold" => $column->bold,
            ];
        }

        foreach ($this->data as $datum) {
            $row = [];

            foreach ($this->columns as $column) {
                $row[] = [
                    "value" => $column->getRender($datum, $this->spreadDatum),
                    "tooltip" => $column->getTooltip() === null ? null : $this->call($column->getTooltip(), $datum, $this->spreadDatum),
                    "raw" => $column::raw,
                    "component" => $column::component,
                    "isActive" => $this->isActive ? $this->call($this->isActive, $datum, $this->spreadDatum) : false,
                    "isDisabled" => $this->isDisabled ? $this->call($this->isDisabled, $datum, $this->spreadDatum) : false,
                ];
            }

            $table["rows"][] = $row;
            $table["numberOfRows"]++;
        }

        return $table;
    }

    private function call(Closure $closure, mixed $datum, bool $spreadDatum): mixed
    {
        if ($spreadDatum) {
            return $closure(...$datum);
        } else {
            return $closure($datum);
        }
    }

    public function getMaxRows(): ?int
    {
        return $this->maxRows;
    }

    public function setMaxRows(?int $maxRows): static
    {
        $this->maxRows = $maxRows;
        return $this;
    }
}