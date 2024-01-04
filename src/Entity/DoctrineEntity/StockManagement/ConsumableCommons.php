<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\StockManagement;

use App\Entity\Rack;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait ConsumableCommons
{
    #[ORM\Column]
    #[Assert\Range(minMessage: "Unit size must be at least 1", min: 1)]
    private int $unitSize = 1;

    #[ORM\Column]
    #[Assert\Range(minMessage: "Number of units must be at least 1", min: 1)]
    private int $numberOfUnits = 1;

    #[ORM\Column]
    private float $pricePerPackage = 0.0;

    #[ORM\ManyToOne(targetEntity: Rack::class)]
    #[ORM\JoinColumn(name: "location_ulid", referencedColumnName: "ulid", nullable: true, onDelete: "SET NULL")]
    private ?Rack $location = null;

    public function getUnitSize(): int
    {
        return $this->unitSize;
    }

    public function setUnitSize(int $unitSize): self
    {
        $this->unitSize = $unitSize;
        return $this;
    }

    public function getNumberOfUnits(): int
    {
        return $this->numberOfUnits;
    }

    public function setNumberOfUnits(int $numberOfUnits): self
    {
        $this->numberOfUnits = $numberOfUnits;
        return $this;
    }

    public function getPricePerPackage(): float
    {
        return $this->pricePerPackage;
    }

    public function setPricePerPackage(float $pricePerPackage): self
    {
        $this->pricePerPackage = $pricePerPackage;
        return $this;
    }

    public function getLocation(): ?Rack
    {
        return $this->location;
    }

    public function setLocation(?Rack $location): self
    {
        $this->location = $location;
        return $this;
    }
}