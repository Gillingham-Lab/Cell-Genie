<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\Fields\NameTrait;
use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RecipeRepository::class)]
#[ORM\UniqueConstraint(fields: ["shortName", "concentrationFactor"])]
#[UniqueEntity(fields: ["shortName", "concentrationFactor"])]
#[Gedmo\Loggable]
class Recipe
{
    use IdTrait;
    use NameTrait;

    #[ORM\Column(type: "float", nullable: false, options: ["default" => 1])]
    #[Assert\Range(min: 0)]
    private float $concentrationFactor = 1.0;

    #[ORM\Column(type: "string", length: 100, nullable: true)]
    #[Assert\Length(min: 1, max: 100)]
    private ?string $category = null;

    #[ORM\Column(name: "pH", type: "float", nullable: true)]
    private ?float $pH = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $comment = null;

    #[ORM\OneToMany(mappedBy: "recipe", targetEntity: RecipeIngredient::class, cascade: ["persist", "remove"])]
    #[Assert\Valid]
    private Collection $ingredients;

    public function __construct()
    {
        $this->generateId();
        $this->ingredients = new ArrayCollection();
    }

    public function __toString(): string
    {
        $name = ($this->getShortName() ?? 'unknown');

        if ($this->getConcentrationFactor() == 1.0) {
            return $name;
        } else {
            return sprintf("%s (%.2fX)", $name, $this->getConcentrationFactor());
        }
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(?string $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getConcentrationFactor(): float
    {
        return $this->concentrationFactor;
    }

    public function setConcentrationFactor(float $factor): self
    {
        $this->concentrationFactor = $factor;

        return $this;
    }

    public function getPH(): ?float
    {
        return $this->pH;
    }

    public function setPH(?float $pH): self
    {
        $this->pH = $pH;
        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return Collection<int, RecipeIngredient>
     */
    public function getIngredients(): Collection
    {
        return $this->ingredients;
    }

    public function addIngredient(RecipeIngredient $ingredient): self
    {
        if (!$this->ingredients->contains($ingredient)) {
            $ingredient->setRecipe($this);
            $this->ingredients[] = $ingredient;
        }

        return $this;
    }

    public function removeIngredient(RecipeIngredient $ingredient): self
    {
        $this->ingredients->removeElement($ingredient);

        return $this;
    }
}