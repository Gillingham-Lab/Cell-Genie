<?php
declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[Gedmo\Loggable]
class EpitopeHost extends Epitope
{
    #[ORM\OneToMany(mappedBy: "hostOrganism", targetEntity: Antibody::class)]
    private Collection $hostAntibodies;

    public function __construct()
    {
        $this->hosts = new ArrayCollection();
    }

    /**
     * @return Collection<int, Antibody>
     */
    public function getHostAntibodies(): Collection
    {
        return $this->hostAntibodies;
    }

    public function addHostAntibody(Antibody $hostTarget): self
    {
        if (!$this->hostAntibodies->contains($hostTarget)) {
            $this->hostAntibodies[] = $hostTarget;
            $hostTarget->setHostOrganism($this);
        }

        return $this;
    }

    public function removeHostAntibody(Antibody $hostTarget): self
    {
        if ($this->hostAntibodies->removeElement($hostTarget)) {
            // set the owning side to null (unless already changed)
            if ($hostTarget->getHostOrganism() === $this) {
                $hostTarget->setHostOrganism(null);
            }
        }

        return $this;
    }
}
