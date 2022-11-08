<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\Lot;

class SubstanceLot implements \JsonSerializable
{
    public function __construct(
        private readonly Substance $substance,
        private readonly Lot $lot,
    ) {
    }

    public function jsonSerialize(): mixed
    {
        return [
            "substance" => $this->substance,
            "lot" => $this->lot,
        ];
    }

    public function getSubstance(): Substance
    {
        return $this->substance;
    }

    public function getLot(): Lot
    {
        return $this->lot;
    }
}