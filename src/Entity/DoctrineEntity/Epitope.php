<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\Fields\ShortNameTrait;
use App\Repository\EpitopeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: EpitopeRepository::class)]
#[ORM\UniqueConstraint(fields: ["shortName"])]
#[UniqueEntity(fields: ["shortName"], message: "The short name of the epitope must be unique.")]
#[Gedmo\Loggable]
class Epitope
{
    use IdTrait;
    use ShortNameTrait;

    /** @var Collection<int, Antibody> */
    #[ORM\ManyToMany(targetEntity: Antibody::class, mappedBy: "epitopeTargets")]
    private Collection $antibodies;

    /** @var Collection<int, Substance> */
    #[ORM\ManyToMany(targetEntity: Substance::class, inversedBy: "epitopes", cascade: ["persist"])]
    #[ORM\JoinTable(name: "substance_epitopes")]
    #[ORM\JoinColumn(name: "epitope_id", referencedColumnName: "id", onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn(name: "substance_ulid", referencedColumnName: "ulid", onDelete: "CASCADE")]
    private Collection $substances;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    public function __construct()
    {
        $this->antibodies = new ArrayCollection();
        $this->substances = new ArrayCollection();
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
            $antibody->addEpitopeTarget($this);
        }

        return $this;
    }

    public function removeAntibody(Antibody $antibody): self
    {
        if ($this->antibodies->removeElement($antibody)) {
            $antibody->removeEpitopeTarget($this);
        }

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /** @return Collection<int, Substance> */
    public function getSubstances(): Collection
    {
        return $this->substances;
    }

    public function addSubstance(Substance $substance): self
    {
        if (!$this->substances->contains($substance)) {
            $this->substances[] = $substance;
            $substance->addEpitope($this);
        }

        return $this;
    }

    public function removeSubstance(Substance $substance): self
    {
        if ($this->substances->removeElement($substance)) {
            $substance->removeEpitope($this);
        }

        return $this;
    }
}
