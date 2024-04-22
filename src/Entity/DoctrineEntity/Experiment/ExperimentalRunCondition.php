<?php

namespace App\Entity\DoctrineEntity\Experiment;

use App\Entity\Traits\Fields\IdTrait;
use App\Repository\DoctrineEntity\Experiment\ExperimentalRunConditionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExperimentalRunConditionRepository::class)]
#[ORM\Table("new_experimental_run_condition")]
class ExperimentalRunCondition
{
    use IdTrait;

    #[ORM\ManyToOne(inversedBy: 'conditions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ExperimentalRun $experimentalRun = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $control = null;

    public function getExperimentalRun(): ?ExperimentalRun
    {
        return $this->experimentalRun;
    }

    public function setExperimentalRun(?ExperimentalRun $experimentalRun): static
    {
        $this->experimentalRun = $experimentalRun;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isControl(): ?bool
    {
        return $this->control;
    }

    public function setControl(bool $control): static
    {
        $this->control = $control;

        return $this;
    }
}
