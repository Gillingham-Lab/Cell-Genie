<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\DoctrineEntity\Storage\Box;
use ErrorException;
use Generator;
use InvalidArgumentException;
use JsonSerializable;

class BoxMap implements JsonSerializable
{
    /** @var array<int, array<int, object|null>> */
    private array $map;

    /** @var object[] */
    private array $loose = [];
    private int $loosePointer = 0;
    private bool $doublyOccupied = false;

    /** @var array<int, array<int, boolean|null>> */
    private array $doublyOccupiedMap;
    private int $count = 0;

    public static function fromBox(Box $box): self
    {
        return new self($box->getRows(), $box->getCols());
    }

    public function __construct(
        private readonly int $rows,
        private readonly int $cols,
    ) {
        $map = [];

        for ($i = 0; $i < $rows; $i++) {
            $row = [];

            for ($j = 0; $j < $cols; $j++) {
                $row[] = null;
            }

            $map[] = $row;
        }

        $this->map = $map;
        $this->doublyOccupiedMap = $map;
    }

    /**
     * @return array<string, mixed[]|scalar>
     */
    public function jsonSerialize(): array
    {
        return [
            "rows" => $this->rows,
            "cols" => $this->cols,
            "count" => $this->count,
            "doublyOccupied" => $this->doublyOccupied,
            "map" => iterator_to_array($this->generateLinearBoxMap()),
        ];
    }

    private function generateLinearBoxMap(): Generator
    {
        for ($i = 0; $i < $this->rows; $i++) {
            for ($j = 0; $j < $this->cols; $j++) {
                // Check if the position is occupied in the box
                if ($this->isOccupied($i, $j)) {
                    $object = $this->get($i, $j);
                    yield new BoxMapPosition($i + 1, $j + 1, $object, $this->isDoublyOccupied($i, $j));
                } else {
                    // If this is not the case, we can iterate from the loose list.
                    if (isset($this->loose[$this->loosePointer])) {
                        $object = $this->loose[$this->loosePointer];
                        yield new BoxMapPosition($i + 1, $j + 1, $object, false, true);
                        $this->loosePointer++;
                    } else {
                        yield new BoxMapPosition($i + 1, $j + 1, null);
                    }
                }
            }
        }
    }

    private function assertCoordinatesWithinBounds(int $row, int $col): void
    {
        if ($row > $this->rows or $row < 0) {
            throw new InvalidArgumentException("Row {$row} is out of bounds for this BoxMap with {$this->rows} rows.");
        }

        if ($col > $this->cols or $col < 0) {
            throw new InvalidArgumentException("Column {$col} is out of bounds for this BoxMap with {$this->cols} columns.");
        }
    }

    /**
     * @param int $row
     * @param int $col
     * @param int $shift
     * @return array{int, int}
     */
    private function shift(int $row, int $col, int $shift = 0): array
    {
        if ($shift > 0) {
            $col += $shift;

            if ($col >= $this->cols) {
                $row += floor($col / $this->cols);
                $col = $col % $this->cols;
            }
        }

        return [(int) $row, $col];
    }

    /**
     * @param int $row row number, 0-index
     * @param int $col col number, 0-index
     * @param object|null $object
     * @param int $shift horizontal shift, automatically wraps
     * @return void
     */
    public function set(int $row, int $col, ?object $object, int $shift = 0): void
    {
        [$row, $col] = $this->shift($row, $col, $shift);

        $this->assertCoordinatesWithinBounds($row, $col);

        if ($this->isOccupied($row, $col)) {
            $this->doublyOccupied = true;
            $this->doublyOccupiedMap[$row][$col] = true;
        }

        $this->count++;
        $this->map[$row][$col] = $object;
    }

    public function get(int $row, int $col, int $shift = 0): ?object
    {
        [$row, $col] = $this->shift($row, $col, $shift);
        $this->assertCoordinatesWithinBounds($row, $col);
        return $this->map[$row][$col];
    }

    public function getAtCoordinate(string $coordinate, int $shift = 0): ?object
    {
        [$row, $col] = (new BoxCoordinate($coordinate))->getIntCoordinates();
        return $this->get($row - 1, $col - 1, $shift);
    }

    public function setAtCoordinate(string $coordinate, ?object $object, int $shift = 0): void
    {
        [$row, $col] = (new BoxCoordinate($coordinate))->getIntCoordinates();
        $this->set($row - 1, $col - 1, $object, $shift);
    }

    public function isOccupied(int $row, int $col, int $shift = 0): bool
    {
        [$row, $col] = $this->shift($row, $col, $shift);
        return $this->map[$row][$col] !== null;
    }

    public function isCoordinateOccupied(string $coordinate, int $shift = 0): bool
    {
        [$row, $col] = (new BoxCoordinate($coordinate))->getIntCoordinates();
        return $this->isOccupied($row, $col, $shift);
    }

    public function isDoublyOccupied(?int $row = null, ?int $col = null): bool
    {
        if ($row and $col) {
            return $this->doublyOccupiedMap[$row][$col] === true;
        } else {
            return $this->doublyOccupied;
        }
    }

    public function addLoose(object $object): void
    {
        $this->count++;
        $this->loose[] = $object;
    }

    public function isFull(): bool
    {
        return ($this->rows * $this->cols - $this->count) <= 0;
    }

    public function isOverfilled(): bool
    {
        return ($this->rows * $this->cols - $this->count) < 0;
    }

    public function getSize(): int
    {
        return $this->rows * $this->cols;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function add(object $object, int $numberOfAliquots, ?string $lotCoordinate): void
    {
        // Do not display lots with no aliquots.
        if ($numberOfAliquots === 0) {
            return;
        }

        // If no coordinate is given, add loose.
        if (empty($lotCoordinate)) {
            for ($i = 0; $i < $numberOfAliquots; $i++) {
                $this->addLoose($object);
            }
        } else {
            for ($i = 0; $i < $numberOfAliquots; $i++) {
                // Try to set at coordinate. If it fails, add loose.
                try {
                    $this->setAtCoordinate($lotCoordinate, $object, shift: $i);
                } catch (InvalidArgumentException | ErrorException) {
                    $this->addLoose($object);
                }
            }
        }
    }
}
