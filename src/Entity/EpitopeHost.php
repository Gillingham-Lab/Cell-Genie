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
    #[ORM\ManyToMany(targetEntity: AntibodyHost::class, cascade: ["persist"])]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private Collection $hosts;

    public function __construct()
    {
        $this->hosts = new ArrayCollection();
    }

    /**
     * @return Collection<int, AntibodyHost>
     */
    public function getHosts(): Collection
    {
        return $this->hosts;
    }

    public function addHost(AntibodyHost $protein): self
    {
        if (!$this->hosts->contains($protein)) {
            $this->hosts->add($protein);
        }

        return $this;
    }

    public function removeHost(AntibodyHost $protein): self
    {
        if ($this->hosts->contains($protein)) {
            $this->hosts->removeElement($protein);
        }

        return $this;
    }
}
