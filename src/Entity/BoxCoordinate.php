<?php
declare(strict_types=1);

namespace App\Entity;

use InvalidArgumentException;

class BoxCoordinate
{
    private readonly int $row;
    private readonly int $col;

    public function __construct(
        private readonly string $coordinate,
    ) {
        $matches = [];
        $matchReturn = preg_match("#^(?P<row>[A-Z]+)(-?)(?P<col>[0-9]+)$#", $coordinate, $matches);

        if ($matchReturn !== 1) {
            throw new InvalidArgumentException("The given parameter '{$coordinate}' is not a valid coordinate.");
        }

        $rowNumber = $this->stringCoordinateToNumber($matches["row"]);
        $colNumber = (int) $matches["col"];

        $this->row = $rowNumber;
        $this->col = $colNumber;
    }

    private function stringCoordinateToNumber(string $stringCoordinate): int
    {
        $length = strlen($stringCoordinate);
        $number = 0;

        for ($i = 0; $i < $length; $i++) {
            $letter = $stringCoordinate[$i];

            $number = $number * 26 + (ord($letter) - ord("A")) + 1;
        }

        return $number;
    }

    public function getCoordinate(): string
    {
        return $this->coordinate;
    }

    /** @return array{int, int} */
    public function getIntCoordinates(): array
    {
        return [$this->row, $this->col];
    }

    public function getRow(): int
    {
        return $this->row;
    }

    public function getCol(): int
    {
        return $this->col;
    }
}
