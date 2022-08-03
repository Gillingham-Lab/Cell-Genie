<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\ExperimentalConditionRepository;
use App\Repository\ExperimentalMeasurementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ExperimentalMeasurementRepository::class)]
#[ORM\UniqueConstraint(columns: ["experiment_id", "title"])]
#[UniqueEntity(["title", "experiment"], message: "The same title cannot be used for two measurements.")]
class ExperimentalMeasurement extends InputType
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\Column(type: "ulid", unique: true)]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id = null;

    #[ORM\ManyToOne(targetEntity: Experiment::class, inversedBy: "measurements")]
    #[ORM\JoinColumn(nullable: true, onDelete: "CASCADE")]
    #[Assert\NotNull]
    private ?Experiment $experiment = null;

    #[ORM\Column(name: "_order", type: "integer", nullable: false, options: ["default" => 0])]
    #[Assert\NotNull]
    private int $order = 0;

    #[ORM\Column(type: "string", length: 100, nullable: false)]
    #[Assert\Length(min: 3, max: 100)]
    private string $title = "";

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: "boolean", nullable: false, options: ["default" => false])]
    private bool $internalStandard = false;

    #[ORM\Column(type: 'boolean', nullable: true)]
    private $isY;

    public function __toString(): string
    {
        return $this->title ?? "{no name}";
    }

    public function getId(): ?Ulid
    {
        return $this->id;
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

    public function getOrder(): int
    {
        return $this->order;
    }

    public function setOrder(?int $order = 0): self
    {
        if ($order === null) {
            $order = 0;
        }

        $this->order = $order;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
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

    public function isInternalStandard(): bool
    {
        return $this->internalStandard;
    }

    public function setInternalStandard(bool $internalStandard = true): self
    {
        $this->internalStandard = $internalStandard;

        return $this;
    }

    public function isIsY(): ?bool
    {
        return $this->isY;
    }

    public function setIsY(?bool $isY): self
    {
        $this->isY = $isY;

        return $this;
    }
}