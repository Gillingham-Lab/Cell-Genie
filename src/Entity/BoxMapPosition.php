<?php
declare(strict_types=1);

namespace App\Entity;

use JsonSerializable;

class BoxMapPosition implements JsonSerializable
{
    public function __construct(
        private readonly int    $row,
        private readonly int    $col,
        private readonly ?object $object,
        private readonly bool   $isDoublyOccupied = false,
        private readonly bool   $isLoose = false,
    ) {}

    /**
     * @return array{
     *     row: int,
     *     col: int,
     *     doublyOccupied: bool,
     *     loose: bool,
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            "row" => $this->row,
            "col" => $this->col,
            "doublyOccupied" => $this->isDoublyOccupied,
            "loose" => $this->isLoose,
            "object" => $this->object,
        ];
    }
}
