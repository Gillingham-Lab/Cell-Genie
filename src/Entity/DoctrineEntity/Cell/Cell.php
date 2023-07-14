<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Cell;

use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Experiment;
use App\Entity\Interface\PrivacyAwareInterface;
use App\Entity\Morphology;
use App\Entity\Organism;
use App\Entity\Tissue;
use App\Entity\Traits\HasAttachmentsTrait;
use App\Entity\Traits\Privacy\GroupOwnerTrait;
use App\Entity\Traits\Privacy\OwnerTrait;
use App\Entity\Traits\Privacy\PrivacyAwareTrait;
use App\Entity\Traits\Privacy\PrivacyLevelTrait;
use App\Entity\Traits\VendorTrait;
use App\Repository\Cell\CellRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CellRepository::class)]
#[UniqueEntity(fields: "cellNumber")]
#[Gedmo\Loggable]
class Cell implements PrivacyAwareInterface
{
    use VendorTrait;
    use HasAttachmentsTrait;
    use PrivacyAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CellGroup::class, cascade: ["persist"], fetch: "LAZY", inversedBy: "cells")]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[ORM\OrderBy(["cellNumber" => "ASC"])]
    #[Gedmo\Versioned]
    #[Assert\NotBlank]
    private ?CellGroup $cellGroup = null;

    #[ORM\Column(type: "string", length: 255, unique: True)]
    #[Assert\Length(max: 250)]
    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    private string $name = "";

    #[ORM\Column(type: "boolean")]
    #[Gedmo\Versioned]
    private bool $isEngineered = false;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $engineeringDescription = null;

    #[ORM\ManyToOne(targetEntity: Plasmid::class)]
    #[ORM\JoinColumn(referencedColumnName: "ulid", nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?Plasmid $engineeringPlasmid = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?User $engineer;

    #[ORM\OneToMany(mappedBy: "parent", targetEntity: Cell::class)]
    private Collection $children;

    #[ORM\ManyToOne(targetEntity: Cell::class, fetch: "EAGER", inversedBy: "children")]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?Cell $parent = null;

    #[ORM\OneToMany(mappedBy: "cell", targetEntity: CellAliquot::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    private Collection $cellAliquotes;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $origin = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $acquiredOn = null;

    #[ORM\Column(type: "decimal", precision: 7, scale: 2, nullable: true)]
    #[Gedmo\Versioned]
    private ?int $price = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?User $boughtBy = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $originComment = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $medium = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $freezing = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $thawing = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $cultureConditions = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $splitting = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Gedmo\Versioned]
    private ?string $trypsin = null;

    #[ORM\ManyToMany(targetEntity: Experiment::class, mappedBy: "cells")]
    private ?Collection $experiments;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $lysing = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $seeding = null;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Gedmo\Versioned]
    private ?int $countOnConfluence = null;

    #[ORM\Column(type: "string", length: 10, unique: True, nullable: true)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 10)]
    #[Gedmo\Versioned]
    private ?string $cellNumber = "???";

    #[ORM\OneToMany(mappedBy: 'cellLine', targetEntity: CellProtein::class, cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\OrderBy(["orderValue" => "ASC"])]
    private Collection $cellProteins;

    public function __construct()
    {
        $this->cellAliquotes = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->experiments = new ArrayCollection();
        $this->attachments = new ArrayCollection();
        $this->associatedProteins = new ArrayCollection();
        $this->cellProteins = new ArrayCollection();
    }

    public function __toString()
    {
        return "{$this->cellNumber} | {$this->getName()}";
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

    public function getIsEngineered(): bool
    {
        return $this->isEngineered;
    }

    public function setIsEngineered(bool $isEngineered): self
    {
        $this->isEngineered = $isEngineered;

        return $this;
    }

    public function getEngineeringDescription(): ?string
    {
        return $this->engineeringDescription;
    }

    public function setEngineeringDescription(?string $description): self
    {
        $this->engineeringDescription = $description;
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
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Cell $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CellAliquot>
     */
    public function getCellAliquotes(): Collection
    {
        return $this->cellAliquotes;
    }

    public function addCellAliquote(CellAliquot $cellAliquote): self
    {
        if (!$this->cellAliquotes->contains($cellAliquote)) {
            $this->cellAliquotes[] = $cellAliquote;
            $cellAliquote->setCell($this);
        }

        return $this;
    }

    public function removeCellAliquote(CellAliquot $cellAliquote): self
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

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(?int $price): self
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

    public function getEngineeringPlasmid(): ?Plasmid
    {
        return $this->engineeringPlasmid;
    }

    public function setEngineeringPlasmid(?Plasmid $engineeringPlasmid): self
    {
        $this->engineeringPlasmid = $engineeringPlasmid;

        return $this;
    }

    public function getEngineer(): ?User
    {
        return $this->engineer;
    }

    public function setEngineer(?User $engineer): self
    {
        $this->engineer = $engineer;
        return $this;
    }

    /**
     * @return Collection<int, CellProtein>
     */
    public function getCellProteins(): Collection
    {
        return $this->cellProteins;
    }

    public function addCellProtein(CellProtein $cellProtein): self
    {
        if (!$this->cellProteins->contains($cellProtein)) {
            $this->cellProteins[] = $cellProtein;
            $cellProtein->setCellLine($this);
        }

        return $this;
    }

    public function removeCellProtein(CellProtein $cellProtein): self
    {
        if ($this->cellProteins->removeElement($cellProtein)) {
            // set the owning side to null (unless already changed)
            if ($cellProtein->getCellLine() === $this) {
                $cellProtein->setCellLine(null);
            }
        }

        return $this;
    }

    // Virtual properties for old systems
    public function getCellosaurusId(): ?string
    {
        return $this->cellGroup->getCellosaurusId();
    }

    public function getAge(): ?string
    {
        return $this->cellGroup->getAge();
    }

    public function getSex(): string
    {
        return $this->cellGroup->getSex();
    }

    public function getEthnicity(): string
    {
        return $this->cellGroup->getEthnicity();
    }

    public function getDisease(): string
    {
        return $this->cellGroup->getDisease();
    }

    public function getCultureType(): string
    {
        return $this->cellGroup->getCultureType();
    }

    public function getIsCancer(): bool
    {
        return $this->cellGroup->getIsCancer();
    }

    public function getMorphology(): ?Morphology
    {
        return $this->cellGroup->getMorphology();
    }

    public function getTissue(): ?Tissue
    {
        return $this->cellGroup->getTissue();
    }

    public function getOrganism(): ?Organism
    {
        return $this->cellGroup->getOrganism();
    }

    public function getRrid(): ?string
    {
        return $this->cellGroup->getRrid();
    }

    public function getCellGroup(): ?CellGroup
    {
        return $this->cellGroup;
    }

    public function setCellGroup(?CellGroup $cellGroup): self
    {
        $this->cellGroup = $cellGroup;
        return $this;
    }
}
