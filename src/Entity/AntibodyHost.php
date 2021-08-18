<?php

namespace App\Entity;

use App\Repository\AntibodyHostRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=AntibodyHostRepository::class)
 */
class AntibodyHost
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="CUSTOM")
     * @ORM\Column(type="ulid", unique=True)
     * @ORM\CustomIdGenerator(class=UlidGenerator::class)
     */
    private ?Ulid $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Minimum length for host name is 3",
        maxMessage: "Maximum length for host name is 255"
    )]
    private ?string $name;

    /**
     * @ORM\OneToMany(targetEntity=Antibody::class, mappedBy="hostOrganism")
     */
    private Collection $primaries;

    /**
     * @ORM\OneToMany(targetEntity=Antibody::class, mappedBy="hostTarget")
     */
    private Collection $secondaries;

    public function __construct()
    {
        $this->primaries = new ArrayCollection();
        $this->secondaries = new ArrayCollection();
    }

    public function __toString(): string
    {
        return "{$this->name}";
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Antibody>
     */
    public function getPrimaries(): Collection
    {
        return $this->primaries;
    }

    public function addPrimary(Antibody $hostTarget): self
    {
        if (!$this->primaries->contains($hostTarget)) {
            $this->primaries[] = $hostTarget;
            $hostTarget->setHostOrganism($this);
        }

        return $this;
    }

    public function removePrimary(Antibody $hostTarget): self
    {
        if ($this->primaries->removeElement($hostTarget)) {
            // set the owning side to null (unless already changed)
            if ($hostTarget->getHostOrganism() === $this) {
                $hostTarget->setHostOrganism(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Antibody>
     */
    public function getSecondaries(): Collection
    {
        return $this->secondaries;
    }

    public function addSecondary(Antibody $secondary): self
    {
        if (!$this->secondaries->contains($secondary)) {
            $this->secondaries[] = $secondary;
            $secondary->setHostTarget($this);
        }

        return $this;
    }

    public function removeSecondary(Antibody $secondary): self
    {
        if ($this->secondaries->removeElement($secondary)) {
            // set the owning side to null (unless already changed)
            if ($secondary->getHostTarget() === $this) {
                $secondary->setHostTarget(null);
            }
        }

        return $this;
    }
}
