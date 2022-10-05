<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\Lot;

class SubstanceLot
{
    function __construct(
        private Substance $substance,
        private Lot $lot,
    ) {
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