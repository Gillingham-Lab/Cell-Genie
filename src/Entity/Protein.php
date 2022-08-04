<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\NewIdTrait;
use App\Entity\Traits\NameTrait;
use App\Repository\ProteinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JetBrains\PhpStorm\Pure;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Polyfill\Intl\Icu\Exception\NotImplementedException;

#[ORM\Entity(repositoryClass: ProteinRepository::class)]
#[UniqueEntity(fields: "shortName")]
#[Gedmo\Loggable]
class Protein
{
    use NewIdTrait;
    use NameTrait;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $proteinAtlasUri = null;

    #[ORM\ManyToMany(targetEntity: Protein::class, mappedBy: "parents")]
    private Collection $children;

    #[ORM\ManyToMany(targetEntity: Protein::class, inversedBy: "children")]
    #[ORM\JoinColumn(name: "protein_parent_ulid", referencedColumnName: "ulid", nullable: true, onDelete: "SET NULL")]
    #[ORM\InverseJoinColumn(name: "protein_child_ulid", referencedColumnName: "ulid", onDelete: "SET NULL")]
    private Collection $parents;

    #[ORM\ManyToMany(targetEntity: Experiment::class, mappedBy: "proteinTargets")]
    #[ORM\JoinColumn(name: "protein_ulid", referencedColumnName: "ulid", nullable: false, onDelete: "CASCADE")]
    private Collection $experiments;

    /*#[ORM\ManyToMany(targetEntity: Antibody::class, mappedBy: "proteinTarget")]
    #[ORM\JoinColumn(name: "protein_ulid", referencedColumnName: "ulid", nullable: false, onDelete: "CASCADE")]*/
    #[ORM\ManyToMany(targetEntity: EpitopeProtein::class, mappedBy: "proteins")]
    #[ORM\JoinColumn(name: "protein_ulid", referencedColumnName: "ulid", nullable: false, onDelete: "CASCADE")]
    private Collection $epitopes;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private $proteinType;

    #[ORM\Column(type: 'text', nullable: true)]
    private $fastaSequence;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Assert\Length(max: 255)]
    private $mutation;

    public function __construct()
    {
        $this->experiments = new ArrayCollection();
        $this->epitopes = new ArrayCollection();
        $this->parents = new ArrayCollection();
        $this->children = new ArrayCollection();
        $this->expressingCells = new ArrayCollection();
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
            $experiment->addProteinTarget($this);
        }

        return $this;
    }

    public function removeExperiment(Experiment $experiment): self
    {
        if ($this->experiments->removeElement($experiment)) {
            $experiment->removeProteinTarget($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, EpitopeProtein>
     */
    public function getEpitopes(): Collection
    {
        return $this->epitopes;
    }

    public function addEpitope(EpitopeProtein $epitope): self
    {
        if (!$this->epitopes->contains($epitope)) {
            $this->epitopes[] = $epitope;
            $epitope->addProtein($this);
        }

        return $this;
    }

    public function removeEpitope(EpitopeProtein $epitope): self
    {
        if ($this->epitopes->removeElement($epitope)) {
            $epitope->removeProtein($this);
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
}
