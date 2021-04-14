<?php

namespace App\Entity;

use App\Repository\AntibodyDilutionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=AntibodyDilutionRepository::class)
 */
class AntibodyDilution
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity=Antibody::class, inversedBy="antibodyDilutions")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private ?Antibody $antibody = null;

    /**
     * @ORM\Column(type="string", length=15)
     */
    private ?string $dilution = "1:1000";

    /**
     * @ORM\ManyToOne(targetEntity=ExperimentType::class, inversedBy="antibodyDilutions")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $experimentType;

    /**
     * @ORM\ManyToOne(targetEntity=Experiment::class, inversedBy="antibodyDilutions")
     * @ORM\JoinColumn(nullable=true, onDelete="CASCADE")
     */
    private $experiment;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAntibody(): ?Antibody
    {
        return $this->antibody;
    }

    public function setAntibody(?Antibody $antibody): self
    {
        $this->antibody = $antibody;

        return $this;
    }

    public function getDilution(): ?string
    {
        return $this->dilution;
    }

    public function setDilution(string $dilution): self
    {
        $this->dilution = $dilution;

        return $this;
    }

    public function getExperimentType(): ?ExperimentType
    {
        return $this->experimentType;
    }

    public function setExperimentType(?ExperimentType $experimentType): self
    {
        $this->experimentType = $experimentType;

        return $this;
    }

    public function getExperiment(): ?Experiment
    {
        return $this->experiment;
    }

    public function setExperiment(?Experiment $experiment): self
    {
        $this->experiment = $experiment;

        return $this;
    }
}
