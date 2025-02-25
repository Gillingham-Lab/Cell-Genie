<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Cell;

use App\Entity\DoctrineEntity\Vocabulary\Morphology;
use App\Entity\DoctrineEntity\Vocabulary\Organism;
use App\Entity\DoctrineEntity\Vocabulary\Tissue;
use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\HasRRID;
use App\Repository\Cell\CellGroupRepository;
use App\Validator\Constraint\NotLooped;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CellGroupRepository::class)]
#[UniqueEntity(fields: "number", message: "This cell line number is already in use.")]
#[UniqueEntity(fields: "name", message: "This cell line group has already been made")]
#[Gedmo\Loggable]
#[NotLooped("parent", "children")]
class CellGroup
{
    use IdTrait;
    use HasRRID;

    #[ORM\Column(type: "string", length: 15, unique: true, nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 1, max: 15)]
    #[Gedmo\Versioned]
    private ?string $number = null;

    #[ORM\Column(type: "string", length: 255, unique: True)]
    #[Assert\Length(max: 250)]
    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    private string $name = "";

    /** @var Collection<int, CellGroup> */
    #[ORM\OneToMany(mappedBy: "parent", targetEntity: CellGroup::class, cascade: ["persist", "refresh"])]
    private Collection $children;

    #[ORM\ManyToOne(targetEntity: CellGroup::class, cascade: ["persist"], fetch: "EXTRA_LAZY", inversedBy: "children")]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?CellGroup $parent = null;

    #[ORM\Column(type: "string", length: 20, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $cellosaurusId = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(max: 250)]
    #[Gedmo\Versioned]
    private ?string $age = "";

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    #[Gedmo\Versioned]
    private ?string $sex;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    #[Assert\Length(max: 50)]
    #[Gedmo\Versioned]
    private ?string $ethnicity;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    #[Gedmo\Versioned]
    private ?string $disease;

    #[ORM\Column(type: "string", length: 255, options: ["default" => "unknown"])]
    #[Assert\Length(max: 250)]
    #[Gedmo\Versioned]
    private string $cultureType = "";

    #[ORM\Column(type: "boolean", options: ["default" => true])]
    #[Gedmo\Versioned]
    private bool $isCancer = true;

    #[ORM\ManyToOne(targetEntity: Morphology::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?Morphology $morphology = null;

    #[ORM\ManyToOne(targetEntity: Organism::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?Organism $organism = null;

    #[ORM\ManyToOne(targetEntity: Tissue::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?Tissue $tissue = null;

    /** @var Collection<int, Cell> */
    #[ORM\OneToMany(mappedBy: "cellGroup", targetEntity: Cell::class, cascade: ["persist"], orphanRemoval: false)]
    private Collection $cells;

    public function __construct()
    {
        $this->cells = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "{$this->getName()} ({$this->getNumber()})";
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(?string $number): self
    {
        $this->number = $number;
        return $this;
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

    public function getParent(): ?CellGroup
    {
        return $this->parent;
    }

    public function setParent(?CellGroup $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, CellGroup>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function getAllChildren(): \Generator
    {
        foreach ($this->children as $child) {
            yield $child;
            yield from $child->getAllChildren();
        }
    }

    public function addChild(CellGroup $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(CellGroup $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getCellosaurusId(): ?string
    {
        return $this->cellosaurusId;
    }

    public function setCellosaurusId(?string $cellosaurusId): self
    {
        $this->cellosaurusId = $cellosaurusId;
        return $this;
    }

    public function getAge(): ?string
    {
        return $this->age;
    }

    public function setAge(?string $age): self
    {
        $this->age = $age;
        return $this;
    }

    public function getSex(): string
    {
        return $this->sex ?? "";
    }

    public function setSex(?string $sex): self
    {
        $this->sex = $sex;
        return $this;
    }

    public function getEthnicity(): string
    {
        return $this->ethnicity ?? "";
    }

    public function setEthnicity(?string $ethnicity): self
    {
        $this->ethnicity = $ethnicity;
        return $this;
    }

    public function getDisease(): string
    {
        return $this->disease ?? "";
    }

    public function setDisease(?string $disease): self
    {
        $this->disease = $disease;
        return $this;
    }

    public function getCultureType(): string
    {
        return $this->cultureType;
    }

    public function setCultureType(?string $cultureType): self
    {
        $this->cultureType = $cultureType ?? "";

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

    /**
     * @return Collection<int, Cell>
     */
    public function getCells(): Collection
    {
        return $this->cells;
    }

    /**
     * @param Cell $cell
     * @return $this
     */
    public function addCell(Cell $cell): self
    {
        if (!$this->cells->contains($cell)) {
            $this->cells->add($cell);

            if ($cell->getCellGroup() !== $this) {
                $cell->getCellGroup()->removeCell($cell);
                $cell->setCellGroup($this);
            }
        }

        return $this;
    }

    /**
     * @param Cell $cell
     * @return $this
     */
    public function removeCell(Cell $cell): self
    {
        if ($this->cells->contains($cell)) {
            $this->cells->removeElement($cell);

            if ($cell->getCellGroup() === $this) {
                $cell->setCellGroup(null);
            }
        }

        return $this;
    }
}