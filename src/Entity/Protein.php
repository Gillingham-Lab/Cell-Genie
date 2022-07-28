<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\NewIdTrait;
use App\Repository\ProteinRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProteinRepository::class)]
#[UniqueEntity(fields: "shortName")]
class Protein
{
    use NewIdTrait;

    #[ORM\Column(type: "string", length: 10)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 1,
        max: 10,
        minMessage: "Must be at least {{ min }} character long.",
        maxMessage: "Only up to {{ max }} characters allowed.",
    )]
    private string $shortName = "";

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 250)]
    private string $longName = "";

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Url]
    private ?string $proteinAtlasUri = null;

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
    }

    public function __toString(): string
    {
        return $this->getShortName() ?? "unknown";
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getLongName(): ?string
    {
        return $this->longName;
    }

    public function setLongName(string $longName): self
    {
        $this->longName = $longName;

        return $this;
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
