<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\VendorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VendorRepository::class)]
class Vendor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\Length(min: 3, max: 250)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: "text")]
    private string $catalogUrl = "";

    #[ORM\Column(type: "boolean")]
    private bool $hasFreeShipping = false;

    #[ORM\Column(type: "boolean")]
    private bool $hasDiscount = false;

    #[ORM\Column(type: "text", nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $isPreferred = false;

    public function __toString(): string
    {
        return $this->getName();
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

    public function getCatalogUrl(): ?string
    {
        return $this->catalogUrl;
    }

    public function setCatalogUrl(string $catalogUrl): self
    {
        $this->catalogUrl = $catalogUrl;

        return $this;
    }

    public function getHasFreeShipping(): ?bool
    {
        return $this->hasFreeShipping;
    }

    public function setHasFreeShipping(bool $hasFreeShipping): self
    {
        $this->hasFreeShipping = $hasFreeShipping;

        return $this;
    }

    public function getHasDiscount(): ?bool
    {
        return $this->hasDiscount;
    }

    public function setHasDiscount(bool $hasDiscount): self
    {
        $this->hasDiscount = $hasDiscount;

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

    public function getIsPreferred(): ?bool
    {
        return $this->isPreferred;
    }

    public function setIsPreferred(bool $isPreferred): self
    {
        $this->isPreferred = $isPreferred;

        return $this;
    }
}
