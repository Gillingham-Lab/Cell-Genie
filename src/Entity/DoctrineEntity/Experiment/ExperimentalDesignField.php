<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Experiment;

use App\Entity\DoctrineEntity\Form\FormRow;
use App\Entity\Traits\Fields\IdTrait;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Repository\Experiment\ExperimentalDesignRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(ExperimentalDesignRepository::class)]
#[ORM\Table("new_experimental_design_field")]
class ExperimentalDesignField
{
    use IdTrait;

    #[ORM\ManyToOne(targetEntity: ExperimentalDesign::class, inversedBy: "fields")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?ExperimentalDesign $design = null;

    #[ORM\OneToOne(targetEntity: FormRow::class, cascade: ["persist", "remove"])]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[Assert\Valid]
    private ?FormRow $formRow = null;

    #[ORM\Column(enumType: ExperimentalFieldRole::class)]
    private ?ExperimentalFieldRole $role = ExperimentalFieldRole::Top;

    #[ORM\Column(type: Types::SMALLINT, nullable: true, options: ["default" => 0])]
    #[Assert\Range(min: -32768, max: 32767)]
    private ?int $weight = 0;

    public function __construct()
    {
        $this->formRow = new FormRow();
    }

    public function getLabel()
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
}