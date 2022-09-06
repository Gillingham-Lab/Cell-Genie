<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\DoctrineEntity\Substance\Chemical;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[Gedmo\Loggable]
class EpitopeSmallMolecule extends Epitope
{
    #[ORM\ManyToMany(targetEntity: Chemical::class, cascade: ["persist"])]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    #[ORM\InverseJoinColumn(referencedColumnName: "ulid", onDelete: "CASCADE")]
    private Collection $chemicals;

    public function __construct()
    {
        $this->chemicals = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "m:" . ($this->getShortName() ?? "unknown");
    }

    /**
     * @return Collection<int, Chemical>
     */
    public function getChemicals(): Collection
    {
        return $this->chemicals;
    }

    public function addChemical(Chemical $chemical): self
    {
        if (!$this->chemicals->contains($chemical)) {
            $this->chemicals->add($chemical);
        }

        return $this;
    }

    public function removeChemical(Chemical $chemical): self
    {
        if ($this->chemicals->contains($chemical)) {
            $this->chemicals->removeElement($chemical);
        }

        return $this;
    }
}