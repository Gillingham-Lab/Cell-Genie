<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity;

use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\Traits\Fields\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
class RecipeIngredient
{
    use IdTrait;

    #[ORM\ManyToOne(targetEntity: Recipe::class, inversedBy: "ingredients")]
    #[ORM\JoinColumn(nullable: false)]
    private ?Recipe $recipe = null;

    #[ORM\ManyToOne(targetEntity: Chemical::class)]
    #[ORM\JoinColumn(name: "chemical_ulid", referencedColumnName: "ulid", nullable: false)]
    #[ORM\OrderBy(["name" => "ASC"])]
    #[Assert\NotBlank]
    private ?Chemical $chemical = null;

    #[ORM\Column(type: "float", nullable: false, options: ["default" => 0.0])]
    #[Assert\Range(min: 0)]
    private float $concentration = 0.0;

    #[ORM\Column(type: "string", length: 10, nullable: false, options: ["default" => "mol/L"])]
    private string $concentration_unit = "mol/L";

    public function __construct()
    {
        $this->generateId();
    }

    public function __toString(): string
    {
        return $this->chemical?->getShortName() ?? "Unknown";
    }

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }

    public function getChemical(): ?Chemical
    {
        return $this->chemical;
    }

    public function setChemical(Chemical $chemical): self
    {
        $this->chemical = $chemical;

        return $this;
    }

    public function getConcentration(): float
    {
        return $this->concentration;
    }

    public function setConcentration(float $concentration): self
    {
        $this->concentration = $concentration;

        return $this;
    }

    public function getConcentrationUnit(): string
    {
        return $this->concentration_unit;
    }

    public function setConcentrationUnit(string $concentration_unit): self
    {
        $this->concentration_unit = $concentration_unit;

        return $this;
    }
}