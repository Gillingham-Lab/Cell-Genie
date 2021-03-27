<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\VendorRepository;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;

/**
 * @ORM\Entity(repositoryClass=VendorRepository::class)
 */
class Vendor
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
    private string $name;

    /**
     * @ORM\Column(type="text")
     */
    private string $catalogUrl = "";

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $hasFreeShipping = false;

    /**
     * @ORM\Column(type="boolean")
     */
    private bool $hasDiscount = false;

    #[Pure]
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
}
