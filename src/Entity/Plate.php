<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use App\Entity\Traits\NameTrait;
use App\Repository\PlateRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

##[ORM\Entity(repositoryClass: PlateRepository::class)]
##[Gedmo\Loggable]
class Plate
{
    use IdTrait;
    use NameTrait;

    #[ORM\ManyToOne(targetEntity: PlateType::class)]
    #[Gedmo\Versioned]
    private ?PlateType $plateType;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Assert\Range(min: 1, max: 128)]
    private ?int $cols;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Assert\Range(min: 1, max: 128)]
    private ?int $rows;

    #[ORM\ManyToOne(targetEntity: PlateSet::class, inversedBy: 'plates')]
    private ?PlateSet $plateSet;

    public function getPlateType(): ?PlateType
    {
        return $this->plateType;
    }

    public function setPlateType(?PlateType $plateType): self
    {
        $this->plateType = $plateType;

        return $this;
    }

    public function getCols(): ?int
    {
        return $this->cols ?? $this->plateType?->getCols();
    }

    public function setCols(?int $cols): self
    {
        $this->cols = $cols;

        return $this;
    }

    public function getRows(): ?int
    {
        return $this->rows ?? $this->plateType?->getRows();
    }

    public function setRows(?int $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    public function getPlateSet(): ?PlateSet
    {
        return $this->plateSet;
    }

    public function setPlateSet(?PlateSet $plateSet): self
    {
        $this->plateSet = $plateSet;

        return $this;
    }
}
