<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Cell;

use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\FormEntity\DetectionEntry;
use App\Entity\Traits\Fields\IdTrait;
use App\Genie\Enums\GeneRegulation;
use App\Repository\Cell\CellProteinRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Dunglas\DoctrineJsonOdm\Type\JsonDocumentType;
use Symfony\Component\Validator\Constraints as Assert;

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

    /** @var null|DetectionEntry[] */
    #[ORM\Column(type: JsonDocumentType::NAME, nullable: true)]
    private ?array $detection = [];

    #[ORM\Column(type: Types::INTEGER, nullable: false, options: ["default" => 0])]
    private int $orderValue = 0;

    #[ORM\Column(length: 20, nullable: true, enumType: GeneRegulation::class)]
    private ?GeneRegulation $geneRegulation = null;

    public function __toString()
    {
        return $this->cellLine . "::" . $this->associatedProtein;
    }

    public function getCellLine(): ?Cell
    {
        return $this->cellLine;
    }

    public function setCellLine(?Cell $cellLine): static
    {
        $this->cellLine = $cellLine;

        return $this;
    }

    public function getAssociatedProtein(): ?Protein
    {
        return $this->associatedProtein;
    }

    public function setAssociatedProtein(?Protein $associatedProtein): static
    {
        $this->associatedProtein = $associatedProtein;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /** @return null|DetectionEntry[] */
    public function getDetection(): ?array
    {
        return $this->detection;
    }

    /**
     * @param null|DetectionEntry[] $detection
     * @return $this
     */
    public function setDetection(?array $detection): static
    {
        $this->detection = $detection;

        return $this;
    }

    public function getOrderValue(): int
    {
        return $this->orderValue;
    }

    public function setOrderValue(int $orderValue): static
    {
        $this->orderValue = $orderValue;
        return $this;
    }

    public function getGeneRegulation(): ?GeneRegulation
    {
        return $this->geneRegulation;
    }

    public function setGeneRegulation(?GeneRegulation $geneRegulation): static
    {
        $this->geneRegulation = $geneRegulation;

        return $this;
    }
}
