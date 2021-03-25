<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\RackRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=RackRepository::class)
 */
class Rack
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $maxBoxes;

    /**
     * @ORM\OneToMany(targetEntity=Box::class, mappedBy="rack")
     */
    private Collection $boxes;

    public function __toString(): string
    {
        return $this->getName() ?? "unknown";
    }

    public function getId(): ?int
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

    public function getMaxBoxes(): ?int
    {
        return $this->maxBoxes;
    }

    public function setMaxBoxes(int $maxBoxes): self
    {
        $this->maxBoxes = $maxBoxes;

        return $this;
    }

    /**
     * @return Collection|Box[]
     */
    public function getBoxes(): Collection
    {
        return $this->boxes;
    }

    public function addBox(Box $box): self
    {
        if (!$this->boxes->contains($box)) {
            $this->boxes[] = $box;
            $box->setRack($this);
        }

        return $this;
    }

    public function removeBox(Box $box): self
    {
        if ($this->boxes->removeElement($box)) {
            // set the owning side to null (unless already changed)
            if ($box->getRack() === $this) {
                $box->setRack(null);
            }
        }

        return $this;
    }
}
