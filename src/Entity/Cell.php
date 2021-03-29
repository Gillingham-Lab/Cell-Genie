<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\CellRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;

/**
 * @ORM\Entity(repositoryClass=CellRepository::class)
 */
class Cell
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length=255, unique=True)
     */
    private string $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $age;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $cultureType;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isCancer = true;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isEngineered = false;

    /**
     * @ORM\OneToMany(targetEntity=Cell::class, mappedBy="parent")
     */
    private Collection $children;

    /**
     * @ORM\ManyToOne(targetEntity=Cell::class, inversedBy="children", fetch="EAGER")
     */
    private ?Cell $parent = null;

    /**
     * @ORM\ManyToOne(targetEntity=Morphology::class)
     */
    private ?Morphology $morphology = null;

    /**
     * @ORM\ManyToOne(targetEntity=Organism::class)
     */
    private ?Organism $organism = null;

    /**
     * @ORM\ManyToOne(targetEntity=Tissue::class)
     */
    private ?Tissue $tissue = null;

    /**
     * @ORM\OneToMany(targetEntity=CellAliquote::class, mappedBy="cell", orphanRemoval=true)
     */
    private $cellAliquotes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $origin = null;

    /**
     * @ORM\ManyToOne(targetEntity=Vendor::class)
     */
    private ?Vendor $vendor = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $vendorPN = null;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $acquiredOn = null;

    /**
     * @ORM\Column(type="decimal", precision=7, scale=2, nullable=true)
     */
    private $price = null;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private ?User $boughtBy = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $originComment = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $medium = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $freezing = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $thawing = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $cultureConditions = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $splitting = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $trypsin = null;

    /**
     * @ORM\ManyToMany(targetEntity=Experiment::class, mappedBy="cells")
     */
    private $experiments;

    public function __construct()
    {
        $this->cellAliquotes = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->experiments = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getName();
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
     * @return Collection|CellAliquote[]
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

    public function getVendor(): ?Vendor
    {
        return $this->vendor;
    }

    public function setVendor(?Vendor $vendor): self
    {
        $this->vendor = $vendor;

        return $this;
    }

    public function getVendorPn(): ?string
    {
        return $this->vendorPN;
    }

    public function setVendorId(?string $vendorPN): self
    {
        $this->vendorPN = $vendorPN;

        return $this;
    }

    public function getAcquiredOn(): ?\DateTimeInterface
    {
        return $this->acquiredOn;
    }

    public function setAcquiredOn(?\DateTimeInterface $acquiredOn): self
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
        if ($this->medium === null and $this->parent) {
            return $this->parent->getMedium();
        }

        return $this->medium;
    }

    public function setMedium(?string $medium): self
    {
        $this->medium = $medium;

        return $this;
    }

    public function getFreezing(): ?string
    {
        if ($this->freezing === null and $this->parent) {
            return $this->parent->getFreezing();
        }

        return $this->freezing;
    }

    public function setFreezing(?string $freezing): self
    {
        $this->freezing = $freezing;

        return $this;
    }

    public function getThawing(): ?string
    {
        if ($this->thawing === null and $this->parent) {
            return $this->parent->getThawing();
        }

        return $this->thawing;
    }

    public function setThawing(?string $thawing): self
    {
        $this->thawing = $thawing;

        return $this;
    }

    public function getCultureConditions(): ?string
    {
        if ($this->cultureConditions === null and $this->parent) {
            return $this->parent->getCultureConditions();
        }

        return $this->cultureConditions;
    }

    public function setCultureConditions(?string $cultureConditions): self
    {
        $this->cultureConditions = $cultureConditions;

        return $this;
    }

    public function getSplitting(): ?string
    {
        if ($this->splitting === null and $this->parent) {
            return $this->parent->getSplitting();
        }

        return $this->splitting;
    }

    public function setSplitting(?string $splitting): self
    {
        $this->splitting = $splitting;

        return $this;
    }

    public function getTrypsin(): ?string
    {
        if ($this->trypsin === null and $this->parent) {
            return $this->parent->getTrypsin();
        }

        return $this->trypsin;
    }

    public function setTrypsin(?string $trypsin): self
    {
        $this->trypsin = $trypsin;

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
}
