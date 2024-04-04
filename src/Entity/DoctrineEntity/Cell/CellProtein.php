<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Cell;

use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\Traits\Fields\IdTrait;
use App\Repository\Cell\CellProteinRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Dunglas\DoctrineJsonOdm\Type\JsonDocumentType;

#[ORM\Entity(repositoryClass: CellProteinRepository::class)]
class CellProtein
{
    use IdTrait;

    #[ORM\ManyToOne(targetEntity: Cell::class, cascade: ["persist", "remove"], inversedBy: 'cellProteins')]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Cell $cellLine = null;

    #[ORM\ManyToOne(targetEntity: Protein::class, fetch: "LAZY")]
    #[ORM\JoinColumn(referencedColumnName: "ulid", nullable: false, onDelete: "CASCADE")]
    private ?Protein $associatedProtein = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: JsonDocumentType::NAME, nullable: true)]
    private ?array $detection = [];

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ["default" => 0])]
    private int $orderValue = 0;

    private ?Collection $detectionCollection = null;

    public function __toString()
    {
        return $this->cellLine . "::" . $this->associatedProtein;
    }

    public function getCellLine(): ?Cell
    {
        return $this->cellLine;
    }

    public function setCellLine(?Cell $cellLine): self
    {
        $this->cellLine = $cellLine;

        return $this;
    }

    public function getAssociatedProtein(): ?Protein
    {
        return $this->associatedProtein;
    }

    public function setAssociatedProtein(?Protein $associatedProtein): self
    {
        $this->associatedProtein = $associatedProtein;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDetection(): ?array
    {
        return $this->detection;
    }

    public function setDetection(?array $detection): self
    {
        $this->detection = $detection;

        return $this;
    }

    public function getOrderValue(): int
    {
        return $this->orderValue;
    }

    public function setOrderValue(int $orderValue): self
    {
        $this->orderValue = $orderValue;
        return $this;
    }
}
