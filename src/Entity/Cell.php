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
     * @ORM\ManyToOne(targetEntity=Cell::class, inversedBy="children")
     */
    private ?Cell $parent = null;

    /**
     * @ORM\ManyToOne(targetEntity=Morphology::class)
     */
    private Morphology $morphology;

    /**
     * @ORM\ManyToOne(targetEntity=Organism::class)
     */
    private Organism $organism;

    /**
     * @ORM\ManyToOne(targetEntity=Tissue::class)
     */
    private Tissue $tissue;

    /**
     * @ORM\OneToMany(targetEntity=CellAliquote::class, mappedBy="cell", orphanRemoval=true)
     */
    private $cellAliquotes;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $origin = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $vendor = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $vendorId = null;

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

    public function __construct()
    {
        $this->cellAliquotes = new ArrayCollection();
        $this->children = new ArrayCollection();
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

    public function getMorphology(): Morphology
    {
        return $this->morphology;
    }

    public function setMorphology(Morphology $morphology): self
    {
        $this->morphology = $morphology;

        return $this;
    }

    public function getTissue(): Tissue
    {
        return $this->tissue;
    }

    public function setTissue(Tissue $tissue): self
    {
        $this->tissue = $tissue;

        return $this;
    }

    public function getOrganism(): Organism
    {
        return $this->organism;
    }

    public function setOrganism(Organism $organism): self
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

    public function getVendor(): ?string
    {
        return $this->vendor;
    }

    public function setVendor(?string $vendor): self
    {
        $this->vendor = $vendor;

        return $this;
    }

    public function getVendorId(): ?string
    {
        return $this->vendorId;
    }

    public function setVendorId(?string $vendorId): self
    {
        $this->vendorId = $vendorId;

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
}
