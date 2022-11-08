<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\AnnotateableInterface;
use App\Entity\File;
use App\Entity\Organism;
use App\Entity\Traits\CommentTrait;
use App\Entity\Traits\HasRRID;
use App\Entity\Traits\LabJournalTrait;
use App\Entity\Traits\NumberTrait;
use App\Entity\Traits\SequenceAnnotationTrait;
use App\Entity\Traits\SequenceTrait;
use App\Entity\User;
use App\Repository\Substance\PlasmidRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlasmidRepository::class)]
#[Gedmo\Loggable]
class Plasmid extends Substance implements AnnotateableInterface
{
    use NumberTrait;
    use CommentTrait;
    use LabJournalTrait;
    use SequenceTrait;
    use SequenceAnnotationTrait;
    use HasRRID;

    #[ORM\ManyToMany(targetEntity: Protein::class)]
    #[ORM\JoinTable(name: "plasmid_expressed_proteins")]
    #[ORM\JoinColumn(name: "plasmid_ulid", referencedColumnName: "ulid", onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn(name: "protein_ulid", referencedColumnName: "ulid", onDelete: "CASCADE")]
    #[ORM\OrderBy(["shortName" => "ASC"])]
    #[Assert\Valid]
    private Collection $expressedProteins;

    #[ORM\OneToMany(mappedBy: "parent", targetEntity: Plasmid::class)]
    private Collection $children;

    #[ORM\ManyToOne(targetEntity: Plasmid::class, fetch: "EAGER", inversedBy: "children")]
    #[ORM\JoinColumn(referencedColumnName: "ulid", nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?Plasmid $parent = null;

    #[ORM\ManyToOne(targetEntity: Organism::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?Organism $expressionIn = null;

    #[ORM\Column(type: "simple_array")]
    #[Gedmo\Versioned]
    private ?array $growthResistance = null;

    #[ORM\Column(type: "simple_array")]
    #[Gedmo\Versioned]
    private ?array $expressionResistance = null;

    #[ORM\Column(type: "boolean")]
    #[Gedmo\Versioned]
    private bool $forProduction = false;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?User $createdBy = null;

    #[ORM\ManyToOne(targetEntity: File::class, cascade: ["persist", "remove"])]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?File $plasmidMap = null;

    public function __construct()
    {
        parent::__construct();

        $this->expressedProteins = new ArrayCollection();
        $this->sequenceAnnotations = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "{$this->getNumber()} | {$this->getShortName()}";
    }

    /**
     * @return Collection<int, Protein>
     */
    public function getExpressedProteins(): Collection
    {
        return $this->expressedProteins;
    }

    public function addExpressedProtein(Protein $protein): self
    {
        if (!$this->expressedProteins->contains($protein)) {
            $this->expressedProteins[] = $protein;
        }

        return $this;
    }

    public function removeExpressedProtein(Protein $protein): self
    {
        $this->expressedProteins->removeElement($protein);

        return $this;
    }

    public function getParent(): ?Plasmid
    {
        return $this->parent;
    }

    public function setParent(?Plasmid $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, Plasmid>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Plasmid $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Plasmid $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }

    public function getExpressionIn(): ?Organism
    {
        return $this->expressionIn;
    }

    public function setExpressionIn(?Organism $expressionIn): self
    {
        $this->expressionIn = $expressionIn;
        return $this;
    }

    public function getGrowthResistance(): ?array
    {
        return $this->growthResistance;
    }

    public function setGrowthResistance(?array $growthResistance): self
    {
        $this->growthResistance = $growthResistance;
        return $this;
    }

    public function getExpressionResistance(): ?array
    {
        return $this->expressionResistance;
    }

    public function setExpressionResistance(?array $expressionResistance): self
    {
        $this->expressionResistance = $expressionResistance;
        return $this;
    }

    public function isForProduction(): bool
    {
        return $this->forProduction;
    }

    public function setForProduction(bool $forProduction): self
    {
        $this->forProduction = $forProduction;
        return $this;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?User $createdBy): self
    {
        $this->createdBy = $createdBy;
        return $this;
    }

    public function getPlasmidMap(): ?File
    {
        return $this->plasmidMap;
    }

    public function setPlasmidMap(?File $plasmidMap): self
    {
        $this->plasmidMap = $plasmidMap;
        return $this;
    }
}