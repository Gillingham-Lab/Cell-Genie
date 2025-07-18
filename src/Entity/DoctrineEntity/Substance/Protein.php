<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Substance;

use App\Entity\DoctrineEntity\Vocabulary\Organism;
use App\Repository\Substance\ProteinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProteinRepository::class)]
#[UniqueEntity(fields: "shortName")]
#[Gedmo\Loggable]
class Protein extends Substance
{
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $proteinAtlasUri = null;

    /** @var Collection<int, Protein> */
    #[ORM\ManyToMany(targetEntity: Protein::class, mappedBy: "parents")]
    private Collection $children;

    /** @var Collection<int, Protein> */
    #[ORM\ManyToMany(targetEntity: Protein::class, inversedBy: "children")]
    #[ORM\JoinColumn(name: "protein_parent_ulid", referencedColumnName: "ulid", nullable: true, onDelete: "SET NULL")]
    #[ORM\InverseJoinColumn(name: "protein_child_ulid", referencedColumnName: "ulid", onDelete: "SET NULL")]
    private Collection $parents;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $proteinType = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $fastaSequence = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private ?string $mutation = null;

    #[ORM\ManyToOne(targetEntity: Organism::class, fetch: "EAGER")]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?Organism $organism = null;

    public function __construct()
    {
        parent::__construct();
        $this->parents = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getShortName() ?? "unknown";
    }

    public function getProteinAtlasUri(): ?string
    {
        return $this->proteinAtlasUri;
    }

    public function setProteinAtlasUri(?string $proteinAtlasUri): self
    {
        $this->proteinAtlasUri = $proteinAtlasUri;

        return $this;
    }

    /**
     * @return Collection<int, Protein>
     */
    public function getParents(): Collection
    {
        return $this->parents;
    }

    public function addParent(Protein $parent): self
    {
        if (!$this->parents->contains($parent)) {
            $this->parents[] = $parent;
            $parent->addChild($this);
        }

        return $this;
    }

    public function removeParent(Protein $parent): self
    {
        if ($this->parents->removeElement($parent) and $parent !== $this) {
            // set the owning side to null (unless already changed)
            if ($parent->getChildren()->contains($this)) {
                $parent->getChildren()->removeElement($this);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Protein>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChildren(Protein $child): self
    {
        return $this->addChild($child);
    }

    public function addChild(Protein $child): self
    {
        if (!$this->children->contains($child) and $child !== $this) {
            $this->children[] = $child;
            $child->addParent($this);
        }

        return $this;
    }

    public function removeChildren(Protein $child): self
    {
        return $this->removeChild($child);
    }

    public function removeChild(Protein $child): self
    {
        if ($this->children->removeElement($child)) {
            $child->removeParent($this);
        }

        return $this;
    }

    public function getProteinType(): ?string
    {
        return $this->proteinType;
    }

    public function setProteinType(?string $proteinType): self
    {
        $this->proteinType = $proteinType;

        return $this;
    }

    public function getFastaSequence(): ?string
    {
        return $this->fastaSequence;
    }

    public function setFastaSequence(?string $fastaSequence): self
    {
        $this->fastaSequence = $fastaSequence;

        return $this;
    }

    public function getMutation(): ?string
    {
        return $this->mutation;
    }

    public function setMutation(?string $mutation): self
    {
        $this->mutation = $mutation;

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
}
