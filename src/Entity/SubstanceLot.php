<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\DoctrineEntity\Lot;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Service\Doctrine\Type\Ulid;

class SubstanceLot
{
    public function __construct(
        private Substance $substance,
        private Lot $lot
    ) {

    }

    public function getId(): ?Ulid
    {
        return $this->lot->getId();
    }

    public function __toString(): string
    {
        return "{$this->substance}.{$this->lot}";
    }

    public function getSubstance(): Substance {
        return $this->substance;
    }

    public function getLot(): Lot {
        return $this->lot;
    }
}