<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Storage;

use App\Entity\Interface\PrivacyAwareInterface;
use App\Entity\Traits\CommentTrait;
use App\Entity\Traits\Fields\NewIdTrait;
use App\Entity\Traits\Fields\VisualisationTrait;
use App\Entity\Traits\Privacy\PrivacyAwareTrait;
use App\Repository\Storage\RackRepository;
use App\Validator\Constraint\NotLooped;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RackRepository::class)]
#[Gedmo\Loggable]
#[NotLooped("parent", "children")]
class Rack implements PrivacyAwareInterface
{
    use NewIdTrait;
    use PrivacyAwareTrait;
    use VisualisationTrait;
    use CommentTrait;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\Length(min: 1, max: 255)]
    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(type: "integer")]
    #[Gedmo\Versioned]
    private ?int $maxBoxes = 0;

    #[ORM\OneToMany(mappedBy: "rack", targetEntity: Box::class)]
    private Collection $boxes;

    #[ORM\OneToMany(mappedBy: "parent", targetEntity: Rack::class)]
    private Collection $children;

    #[ORM\ManyToOne(targetEntity: Rack::class, fetch: "EXTRA_LAZY", inversedBy: "children")]
    #[ORM\JoinColumn(referencedColumnName: "ulid", nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?Rack $parent = null;

    #[ORM\Column(type: Types::STRING, length: 100, nullable: true)]
    private ?string $pinCode = null;

    // Transient properties only sometimes present
    private ?int $depth = null;
    /** @var array<string> */
    private array $ulidTree = [];
    /** @var array<string> */
    private array $nameTree = [];

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->boxes = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName() ?? "unknown";
    }

    public function getPathName(): ? string
    {
        if ($this->getParent() and substr_count($this->getParent()->getPathName(), " | ") < 10) {
            $name = $this->getParent()->getPathName() . " | ";
        } else {
            $name = "";
        }

        $name .= $this->getName();

        return $name;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getMaxBoxes(): int
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
        $parent?->addChild($this);

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

    /**
     * @return int|null
     */
    public function getDepth(): ?int
    {
        return $this->depth;
    }

    public function setDepth(?int $depth): self
    {
        $this->depth = $depth;
        return $this;
    }

    /**
     * @return array<string>
     */
    public function getUlidTree(): array
    {
        return $this->ulidTree;
    }

    public function setUlidTree(array $ulidTree): self
    {
        $this->ulidTree = $ulidTree;
        return $this;
    }

    /**
     * @return array<string>
     */
    public function getNameTree(): array
    {
        return $this->nameTree;
    }

    public function setNameTree(array $nameTree): self
    {
        $this->nameTree = $nameTree;
        return $this;
    }

    public function getPinCode(): ?string
    {
        return $this->pinCode;
    }

    public function setPinCode(?string $pinCode): self
    {
        $this->pinCode = $pinCode;
        return $this;
    }
}
