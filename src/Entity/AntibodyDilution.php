<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\AntibodyDilutionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AntibodyDilutionRepository::class)]
class AntibodyDilution
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: "ulid", unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id = null;

    #[ORM\ManyToOne(targetEntity: Antibody::class, inversedBy: "antibodyDilutions")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    private ?Antibody $antibody = null;

    #[ORM\Column(type: "string", length: 15)]
    #[Assert\Length(max: 15)]
    #[Assert\NotBlank]
    private ?string $dilution = "1:1000";

    #[ORM\ManyToOne(targetEntity: Experiment::class, inversedBy: "antibodyDilutions")]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    private $experiment;

    public function getId(): ?Ulid
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
