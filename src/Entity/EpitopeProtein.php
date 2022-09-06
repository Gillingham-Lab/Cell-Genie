<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\DoctrineEntity\Substance\Protein;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[Gedmo\Loggable]
class EpitopeProtein extends Epitope
{
    #[ORM\ManyToMany(targetEntity: Protein::class, inversedBy: "epitopes", cascade: ["persist"])]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn(referencedColumnName: "ulid", onDelete: "CASCADE")]
    private Collection $proteins;

    public function __construct()
    {
        $this->proteins = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "p:" . ($this->getShortName() ?? "unknown");
    }

    /**
     * @return Collection<int, Protein>
     */
    public function getProteins(): Collection
    {
        return $this->proteins;
    }

    public function addProtein(Protein $protein): self
    {
        if (!$this->proteins->contains($protein)) {
            $this->proteins->add($protein);
        }

        return $this;
    }

    public function removeProtein(Protein $protein): self
    {
        if ($this->proteins->contains($protein)) {
            $this->proteins->removeElement($protein);
        }

        return $this;
    }
}