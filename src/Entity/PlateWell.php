<?php

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use App\Repository\PlateWellRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlateWellRepository::class)]
class PlateWell
{
    use IdTrait;

    #[ORM\ManyToOne(targetEntity: PlateType::class, inversedBy: 'wells')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PlateType $plate;

    #[ORM\Column(type: 'integer')]
    private int $volume;

    #[ORM\Column(type: 'integer')]
    private int $location;

    public function getPlate(): ?PlateType
    {
        return $this->plate;
    }

    public function setPlate(?PlateType $plate): self
    {
        $this->plate = $plate;

        return $this;
    }

    public function getVolume(): ?int
    {
        return $this->volume;
    }

    public function setVolume(int $volume): self
    {
        $this->volume = $volume;

        return $this;
    }

    public function getLocation(): ?int
    {
        return $this->location;
    }

    public function setLocation(int $location): self
    {
        $this->location = $location;

        return $this;
    }
}
