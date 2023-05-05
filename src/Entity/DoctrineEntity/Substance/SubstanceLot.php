<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\Interface\GroupAwareInterface;
use App\Entity\Lot;
use App\Entity\Traits\GroupOwnerTrait;
use App\Entity\Traits\OwnerTrait;

class SubstanceLot implements \JsonSerializable, GroupAwareInterface
{
    use OwnerTrait;
    use GroupOwnerTrait;

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