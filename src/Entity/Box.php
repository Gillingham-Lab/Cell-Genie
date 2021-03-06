<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\BoxRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BoxRepository::class)]
class Box
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Bux name must contain at least 3 characters",
        maxMessage: "Only 255 characters allowed"
    )]
    #[Assert\NotBlank]
    private ?string $name;

    #[ORM\Column(type: "integer")]
    #[Assert\GreaterThan(value: 0)]
    private ?int $rows = 1;

    #[ORM\Column(type: "integer")]
    #[Assert\GreaterThan(value: 0)]
    private ?int $cols = 1;

    #[ORM\ManyToOne(targetEntity: Rack::class, fetch: "EAGER", inversedBy: "boxes")]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Assert\NotBlank]
    private ?Rack $rack = null;

    public function __construct()
    {
    }

    public function __toString(): string
    {
        return $this->getFullLocation();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFullLocation(): string
    {
        return "{$this->rack->getName()}/{$this->name}";
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

    public function getRows(): ?int
    {
        return $this->rows;
    }

    public function setRows(int $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    public function getCols(): ?int
    {
        return $this->cols;
    }

    public function setCols(int $cols): self
    {
        $this->cols = $cols;

        return $this;
    }

    public function getRack(): ?Rack
    {
        return $this->rack;
    }

    public function setRack(Rack $rack): self
    {
        $this->rack = $rack;

        return $this;
    }
}
