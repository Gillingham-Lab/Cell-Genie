<?php

namespace App\Entity;

use App\Entity\Traits\IdTrait;
use App\Entity\Traits\NameTrait;
use App\Repository\PlateSetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PlateSetRepository::class)]
class PlateSet
{
    use IdTrait;
    use NameTrait;

    #[ORM\OneToMany(mappedBy: 'plateSet', targetEntity: Plate::class)]
    private Collection $plates;

    public function __construct()
    {
        $this->plates = new ArrayCollection();
    }

    /**
     * @return Collection<int, Plate>
     */
    public function getPlates(): Collection
    {
        return $this->plates;
    }

    public function addPlate(Plate $plate): self
    {
        if (!$this->plates->contains($plate)) {
            $this->plates[] = $plate;
            $plate->setPlateSet($this);
        }

        return $this;
    }

    public function removePlate(Plate $plate): self
    {
        if ($this->plates->removeElement($plate)) {
            // set the owning side to null (unless already changed)
            if ($plate->getPlateSet() === $this) {
                $plate->setPlateSet(null);
            }
        }

        return $this;
    }
}
