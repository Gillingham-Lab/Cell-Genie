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
    #[ORM\InverseJoinColumn(name: "protein_child_ulid", referencedColumnName: "ulid")]
    private Collection $parents;

    #[ORM\ManyToMany(targetEntity: Experiment::class, mappedBy: "proteinTargets")]
    #[ORM\JoinColumn(name: "protein_ulid", referencedColumnName: "ulid", nullable: false, onDelete: "CASCADE")]
    private Collection $experiments;

    #[ORM\ManyToMany(targetEntity: Antibody::class, mappedBy: "proteinTarget")]
    #[ORM\JoinColumn(name: "protein_ulid", referencedColumnName: "ulid", nullable: false, onDelete: "CASCADE")]
    private Collection $antibodies;

    public function __construct()
    {
        $this->experiments = new ArrayCollection();
        $this->antibodies = new ArrayCollection();
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

    public function removeParent(Cell $parent): self
    {
        if ($this->parents->removeElement($parent)) {
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

    public function addChild(Protein $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->addParent($this);
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
     * @return Collection<int, Antibody>
     */
    public function getAntibodies(): Collection
    {
        return $this->antibodies;
    }

    public function addAntibody(Antibody $antibody): self
    {
        if (!$this->antibodies->contains($antibody)) {
            $this->antibodies[] = $antibody;
            $antibody->addProteinTarget($this);
        }

        return $this;
    }

    public function removeAntibody(Antibody $antibody): self
    {
        if ($this->antibodies->removeElement($antibody)) {
            $antibody->removeProteinTarget($this);
        }

        return $this;
    }
}
