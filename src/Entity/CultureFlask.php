<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\CultureFlaskRepository;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CultureFlaskRepository::class)]
class CultureFlask
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 250)]
    private string $name;

    #[ORM\Column(type: "smallint", options: ["default" => 1])]
    #[Assert\NotBlank]
    #[Assert\Range(
        notInRangeMessage: "Value must be between {{ min }} and {{ max }}.",
        min: 1,
        max: 100
    )]
    private int $rows = 1;

    #[ORM\Column(type: "smallint", options: ["default" => 1])]
    #[Assert\NotBlank]
    #[Assert\Range(
        notInRangeMessage: "Value must be between {{ min }} and {{ max }}.",
        min: 1,
        max: 100
    )]
    private int $cols = 1;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $comment = "";

    #[ORM\ManyToOne(targetEntity: Vendor::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    private ?Vendor $vendor = null;

    #[ORM\Column(type: "integer", nullable: true)]
    private ?string $vendorId = null;

    #[Pure]
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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getVendor(): ?Vendor
    {
        return $this->vendor;
    }

    public function setVendor(?Vendor $vendor): self
    {
        $this->vendor = $vendor;

        return $this;
    }

    public function getVendorId(): ?string
    {
        return $this->vendorId;
    }

    public function setVendorId(?string $vendorId): self
    {
        $this->vendorId = $vendorId;

        return $this;
    }
}
