<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\HasAttachmentsTrait;
use App\Entity\Traits\VendorTrait;
use App\Repository\CellRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CellRepository::class)]
#[UniqueEntity(fields: "cellNumber")]
class Cell
{
    use VendorTrait;
    use HasAttachmentsTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255, unique: True)]
    #[Assert\Length(max: 250)]
    private string $name = "";

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\Length(max: 250)]
    private string $age = "";

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\Length(max: 250)]
    private string $cultureType = "";

    #[ORM\Column(type: "boolean")]
    private bool $isCancer = true;

    #[ORM\Column(type: "boolean")]
    private bool $isEngineered = false;

    #[ORM\OneToMany(mappedBy: "parent", targetEntity: Cell::class)]
    private Collection $children;

    #[ORM\ManyToOne(targetEntity: Cell::class, fetch: "EAGER", inversedBy: "children")]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Cell $parent = null;

    #[ORM\ManyToOne(targetEntity: Morphology::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Morphology $morphology = null;

    #[ORM\ManyToOne(targetEntity: Organism::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Organism $organism = null;

    #[ORM\ManyToOne(targetEntity: Tissue::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Tissue $tissue = null;

    #[ORM\OneToMany(mappedBy: "cell", targetEntity: CellAliquote::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $cellAliquotes;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $origin = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    private ?DateTimeInterface $acquiredOn = null;

    #[ORM\Column(type: "decimal", precision: 7, scale: 2, nullable: true)]
    private ?int $price = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?User $boughtBy = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $originComment = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private ?string $medium = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $freezing = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $thawing = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $cultureConditions = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $splitting = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $trypsin = null;

    #[ORM\ManyToMany(targetEntity: Experiment::class, mappedBy: "cells")]
    private ?Collection $experiments;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $lysing = null;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $seeding = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?int $countOnConfluence = null;

    #[ORM\Column(type: "string", length: 10, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 10)]
    private ?string $cellNumber = "???";

    public function __construct()
    {
        $this->cellAliquotes = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->experiments = new ArrayCollection();
    }

    public function __toString()
    {
        return "{$this->cellNumber} | {$this->name}";
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

    public function getAge(): ?string
    {
        return $this->age;
    }

    public function setAge(string $age): self
    {
        $this->age = $age;

        return $this;
    }

    public function getCultureType(): string
    {
        return $this->cultureType;
    }

    public function setCultureType(string $cultureType): self
    {
        $this->cultureType = $cultureType;

        return $this;
    }

    public function getIsCancer(): bool
    {
        return $this->isCancer;
    }

    public function setIsCancer(bool $isCancer): self
    {
        $this->isCancer = $isCancer;

        return $this;
    }

    public function getIsEngineered(): bool
    {
        return $this->isEngineered;
    }

    public function setIsEngineered(bool $isEngineered): self
    {
        $this->isEngineered = $isEngineered;

        return $this;
    }

    public function getMorphology(): ?Morphology
    {
        return $this->morphology;
    }

    public function setMorphology(?Morphology $morphology): self
    {
        $this->morphology = $morphology;

        return $this;
    }

    public function getTissue(): ?Tissue
    {
        return $this->tissue;
    }

    public function setTissue(?Tissue $tissue): self
    {
        $this->tissue = $tissue;

        return $this;
    }

    public function getOrganism(): ?Organism
    {
        return $this->organism;
    }

    public function setOrganism(?Organism $organism): self
    {
        $this->organism = $organism;

        return $this;
    }

    public function getParent(): ?Cell
    {
        return $this->parent;
    }

    public function setParent(?Cell $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, Cell>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Cell $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setCell($this);
        }

        return $this;
    }

    public function removeChild(Cell $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getCell() === $this) {
                $child->setCell(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CellAliquote>
     */
    public function getCellAliquotes(): Collection
    {
        return $this->cellAliquotes;
    }

    public function addCellAliquote(CellAliquote $cellAliquote): self
    {
        if (!$this->cellAliquotes->contains($cellAliquote)) {
            $this->cellAliquotes[] = $cellAliquote;
            $cellAliquote->setCell($this);
        }

        return $this;
    }

    public function removeCellAliquote(CellAliquote $cellAliquote): self
    {
        if ($this->cellAliquotes->removeElement($cellAliquote)) {
            // set the owning side to null (unless already changed)
            if ($cellAliquote->getCell() === $this) {
                $cellAliquote->setCell(null);
            }
        }

        return $this;
    }

    public function getOrigin(): ?string
    {
        return $this->origin;
    }

    public function setOrigin(?string $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getAcquiredOn(): ?DateTimeInterface
    {
        return $this->acquiredOn;
    }

    public function setAcquiredOn(?DateTimeInterface $acquiredOn): self
    {
        $this->acquiredOn = $acquiredOn;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getBoughtBy(): ?User
    {
        return $this->boughtBy;
    }

    public function setBoughtBy(?User $boughtBy): self
    {
        $this->boughtBy = $boughtBy;

        return $this;
    }

    public function getOriginComment(): ?string
    {
        return $this->originComment;
    }

    public function setOriginComment(?string $originComment): self
    {
        $this->originComment = $originComment;

        return $this;
    }

    public function getMedium(): ?string
    {
        return $this->medium ?? $this->parent?->getMedium();
    }

    public function setMedium(?string $medium): self
    {
        $this->medium = $medium;

        return $this;
    }

    public function getFreezing(): ?string
    {
        return $this->freezing ?? $this->parent?->getFreezing();
    }

    public function setFreezing(?string $freezing): self
    {
        $this->freezing = $freezing;

        return $this;
    }

    public function getThawing(): ?string
    {
        return $this->thawing ?? $this->parent?->getThawing();
    }

    public function setThawing(?string $thawing): self
    {
        $this->thawing = $thawing;

        return $this;
    }

    public function getCultureConditions(): ?string
    {
        return $this->cultureConditions ?? $this->parent?->getCultureConditions();
    }

    public function setCultureConditions(?string $cultureConditions): self
    {
        $this->cultureConditions = $cultureConditions;

        return $this;
    }

    public function getSplitting(): ?string
    {
        return $this->splitting ?? $this->parent?->getSplitting();
    }

    public function setSplitting(?string $splitting): self
    {
        $this->splitting = $splitting;

        return $this;
    }

    public function getTrypsin(): ?string
    {
        return $this->trypsin ?? $this->parent?->getTrypsin();
    }

    public function setTrypsin(?string $trypsin): self
    {
        $this->trypsin = $trypsin;

        return $this;
    }

    /**
     * @return Collection<int, Experiment>
     */
    public function getExperiments(): Collection
    {
        return $this->experiments;
    }

    public function addExperiment(Experiment $experiment): self
    {
        if (!$this->experiments->contains($experiment)) {
            $this->experiments[] = $experiment;
            $experiment->addCell($this);
        }

        return $this;
    }

    public function removeExperiment(Experiment $experiment): self
    {
        if ($this->experiments->removeElement($experiment)) {
            $experiment->removeCell($this);
        }

        return $this;
    }

    public function getLysing(): ?string
    {
        return $this->lysing ?? $this->parent?->getLysing();
    }

    public function setLysing(?string $lysing): self
    {
        $this->lysing = $lysing;

        return $this;
    }

    public function getSeeding(): ?string
    {
        return $this->seeding ?? $this->parent?->getSeeding();
    }

    public function setSeeding(?string $seeding): self
    {
        $this->seeding = $seeding;

        return $this;
    }

    public function getCountOnConfluence(): ?int
    {
        return $this->countOnConfluence ?? $this->parent?->getCountOnConfluence();
    }

    public function setCountOnConfluence(?int $countOnConfluence): self
    {
        $this->countOnConfluence = $countOnConfluence;

        return $this;
    }

    public function getCellNumber(): string
    {
        return $this->cellNumber ?? "???";
    }

    public function setCellNumber(string $cellNumber): self
    {
        $this->cellNumber = $cellNumber;

        return $this;
    }
}
