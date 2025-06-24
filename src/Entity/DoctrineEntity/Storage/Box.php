<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Storage;

use App\Entity\Interface\PrivacyAwareInterface;
use App\Entity\Traits\Fields\NewIdTrait;
use App\Entity\Traits\Privacy\PrivacyAwareTrait;
use App\Repository\Storage\BoxRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BoxRepository::class)]
#[Gedmo\Loggable]
class Box implements PrivacyAwareInterface
{
    use NewIdTrait;
    use PrivacyAwareTrait;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Box name must contain at least 3 characters",
        maxMessage: "Only 255 characters allowed"
    )]
    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    #[Groups(["box"])]
    private ?string $name = null;

    #[ORM\Column(type: "integer")]
    #[Assert\GreaterThan(value: 0)]
    #[Gedmo\Versioned]
    #[Groups(["box"])]
    private ?int $rows = 1;

    #[ORM\Column(type: "integer")]
    #[Assert\GreaterThan(value: 0)]
    #[Gedmo\Versioned]
    #[Groups(["box"])]
    private ?int $cols = 1;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    #[Groups(["box"])]
    private ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Rack::class, fetch: "LAZY", inversedBy: "boxes")]
    #[ORM\JoinColumn(name: "rack_ulid", referencedColumnName: "ulid", nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?Rack $rack = null;

    public function __construct()
    {
    }

    public function __toString(): string
    {
        return $this->getFullLocation() . " ({$this->getRows()} × {$this->getCols()})";
    }

    public function getFullLocation(): string
    {
        $name = $this->name ?? "no name";
        if ($this->rack) {
            return "{$this->rack->getPathName()} | {$name}";
        } else {
            return "no rack | {$name}";
        }
    }

    public function getPathName(): string
    {
        if ($this->rack) {
            return $this->rack->getPathName();
        } else {
            return "no rack";
        }
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getRows(): int
    {
        return $this->rows;
    }

    public function setRows(int $rows): static
    {
        $this->rows = $rows;

        return $this;
    }

    public function getCols(): int
    {
        return $this->cols;
    }

    public function setCols(int $cols): static
    {
        $this->cols = $cols;

        return $this;
    }

    public function getRack(): ?Rack
    {
        return $this->rack;
    }

    public function setRack(?Rack $rack): static
    {
        $this->rack = $rack;
        $rack?->addBox($this);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }
}
