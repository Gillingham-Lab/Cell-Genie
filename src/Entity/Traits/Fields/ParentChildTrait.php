<?php
declare(strict_types=1);

namespace App\Entity\Traits\Fields;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

trait ParentChildTrait
{
    #[ORM\ManyToOne(targetEntity: self::class, fetch: "EAGER", inversedBy: "children")]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?self $parent = null;

    #[ORM\OneToMany(mappedBy: "parent", targetEntity: self::class)]
    private Collection $children;

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildren(): Collection
    {
        return $this->children;
    }

    public function addChild(self $child): self
    {
        if (!$this->children->contains($child)) {
            $this->children[] = $child;

            $child->setParent($this);
        }

        return $this;
    }

    public function removeChild(self $child): self
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