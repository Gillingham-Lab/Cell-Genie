<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\ExperimentRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExperimentRepository::class)
 */
class Experiment
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="experiments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity=ExperimentType::class, inversedBy="experiments")
     * @ORM\JoinColumn(nullable=false)
     */
    private $experimentType;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

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
}
