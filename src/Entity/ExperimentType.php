<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\ExperimentTypeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExperimentTypeRepository::class)
 */
class ExperimentType
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
    private ?string $name;

    /**
     * @ORM\OneToMany(targetEntity=Experiment::class, mappedBy="experimentType", orphanRemoval=true)
     */
    private ?Collection $experiments;

    /**
     * @ORM\ManyToOne(targetEntity=ExperimentType::class, inversedBy="children")
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity=ExperimentType::class, mappedBy="parent")
     */
    private $children;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $wellplate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $lysing;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $seeding;

    public function __construct()
    {
        $this->experiments = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName() ?? "unknown";
    }

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

    /**
     * @return Collection|Experiment[]
     */
    public function getExperiments(): Collection
    {
        return $this->experiments;
    }

    public function addExperiment(Experiment $experiment): self
    {
        if (!$this->experiments->contains($experiment)) {
            $this->experiments[] = $experiment;
            $experiment->setExperimentType($this);
        }

        return $this;
    }

    public function removeExperiment(Experiment $experiment): self
    {
        if ($this->experiments->removeElement($experiment)) {
            // set the owning side to null (unless already changed)
            if ($experiment->getExperimentType() === $this) {
                $experiment->setExperimentType(null);
            }
        }

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection|self[]
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

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

    public function getWellplate(): ?string
    {
        return $this->wellplate;
    }

    public function setWellplate(?string $wellplate): self
    {
        $this->wellplate = $wellplate;

        return $this;
    }

    public function getLysing(): ?string
    {
        return $this->lysing;
    }

    public function setLysing(?string $lysing): self
    {
        $this->lysing = $lysing;

        return $this;
    }

    public function getSeeding(): ?string
    {
        return $this->seeding;
    }

    public function setSeeding(?string $seeding): self
    {
        $this->seeding = $seeding;

        return $this;
    }
}
