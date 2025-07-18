<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Experiment;

use App\Entity\DoctrineEntity\Form\FormRow;
use App\Entity\Traits\Fields\IdTrait;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Genie\Enums\ExperimentalFieldVariableRoleEnum;
use App\Repository\Experiment\ExperimentalDesignFieldRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(ExperimentalDesignFieldRepository::class)]
#[ORM\Table("new_experimental_design_field")]
class ExperimentalDesignField
{
    use IdTrait;

    #[ORM\ManyToOne(targetEntity: ExperimentalDesign::class, inversedBy: "fields")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?ExperimentalDesign $design = null;

    #[ORM\OneToOne(targetEntity: FormRow::class, cascade: ["persist", "remove"], fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[Assert\Valid]
    private ?FormRow $formRow = null;

    #[ORM\Column(enumType: ExperimentalFieldRole::class)]
    private ?ExperimentalFieldRole $role = ExperimentalFieldRole::Top;

    #[ORM\Column(nullable: true, enumType: ExperimentalFieldVariableRoleEnum::class)]
    #[Assert\NotBlank]
    private ?ExperimentalFieldVariableRoleEnum $variableRole = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, options: ["default" => 0])]
    #[Assert\Range(min: -32768, max: 32767)]
    private ?int $weight = 0;

    #[ORM\Column(type: Types::BOOLEAN, options: ["default" => false])]
    private ?bool $exposed = null;

    #[ORM\Column(type: Types::BOOLEAN, options: ["default" => false])]
    private ?bool $referenced = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $referenceValue = null;

    public function __construct()
    {
        $this->formRow = new FormRow();
    }

    public function __toString(): string
    {
        return $this->getLabel() ?? "(new entry)";
    }

    public function getLabel(): ?string
    {
        return $this->formRow->getLabel();
    }

    public function getDesign(): ?ExperimentalDesign
    {
        return $this->design;
    }

    public function setDesign(?ExperimentalDesign $design): self
    {
        $this->design = $design;
        return $this;
    }

    public function getFormRow(): ?FormRow
    {
        return $this->formRow;
    }

    public function setFormRow(?FormRow $formRow): self
    {
        $this->formRow = $formRow;
        return $this;
    }

    public function getRole(): ?ExperimentalFieldRole
    {
        return $this->role;
    }

    public function setRole(?ExperimentalFieldRole $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(?int $weight): self
    {
        $this->weight = $weight;
        return $this;
    }

    public function getVariableRole(): ?ExperimentalFieldVariableRoleEnum
    {
        return $this->variableRole;
    }

    public function setVariableRole(?ExperimentalFieldVariableRoleEnum $variableRole): static
    {
        $this->variableRole = $variableRole;
        return $this;
    }

    public function isExposed(): bool
    {
        return $this->exposed;
    }

    public function setExposed(bool $exposed): static
    {
        $this->exposed = $exposed;
        return $this;
    }

    public function isReferenced(): ?bool
    {
        return $this->referenced;
    }

    public function setReferenced(bool $referenced): static
    {
        $this->referenced = $referenced;
        return $this;
    }

    public function getReferenceValue(): ?string
    {
        return $this->referenceValue;
    }

    public function setReferenceValue(?string $referenceValue): static
    {
        $this->referenceValue = $referenceValue;
        return $this;
    }
}
