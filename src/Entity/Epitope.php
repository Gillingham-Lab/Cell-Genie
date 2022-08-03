<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use App\Entity\Traits\NameTrait;
use App\Entity\Traits\ShortNameTrait;
use App\Repository\EpitopeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EpitopeRepository::class)]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "epitope_type", type: "string")]
#[ORM\UniqueConstraint(fields: ["shortName"])]
#[UniqueEntity(fields: ["shortName"], message: "The short name of the epitope must be unique.")]
#[Gedmo\Loggable]
class Epitope
{
    use IdTrait;
    use ShortNameTrait;

    #[ORM\ManyToMany(targetEntity: Antibody::class, mappedBy: "epitopeTargets")]
    private Collection $antibodies;

    #[ORM\Column(type: 'text', nullable: true)]
    private $description;

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
}
