<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\Traits\NewIdTrait;
use App\Repository\RackRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RackRepository::class)]
#[Gedmo\Loggable]
class Rack
{
    use NewIdTrait;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\Length(min: 5, max: 255)]
    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    private ?string $name = "";

    #[ORM\Column(type: "integer")]
    #[Gedmo\Versioned]
    private ?int $maxBoxes = 0;

    #[ORM\OneToMany(mappedBy: "rack", targetEntity: Box::class)]
    private Collection $boxes;

    #[ORM\OneToMany(mappedBy: "parent", targetEntity: Rack::class)]
    private Collection $children;

    #[ORM\ManyToOne(targetEntity: Rack::class, fetch: "LAZY", inversedBy: "children")]
    #[ORM\JoinColumn(referencedColumnName: "ulid", nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?Rack $parent = null;

    public function __toString(): string
    {
        return $this->getName() ?? "unknown";
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
     * @return Collection<int, Box>
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

    public function getParent(): ?Rack
    {
        return $this->parent;
    }

    public function setParent(?Rack $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, Rack>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(Rack $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;
            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(Rack $child): self
    {
        if ($this->children->removeElement($child)) {
            // set the owning side to null (unless already changed)
            if ($child->getParent() === $this) {
                $child->setParent(null);
            }
        }

        return $this;
    }
}
