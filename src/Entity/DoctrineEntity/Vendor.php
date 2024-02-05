<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity;

use App\Entity\Traits\Privacy\PrivacyAwareTrait;
use App\Repository\VendorRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: VendorRepository::class)]
#[Gedmo\Loggable]
class Vendor
{
    use PrivacyAwareTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\Length(min: 3, max: 250)]
    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    private ?string $name = null;

    #[ORM\Column(type: "text")]
    #[Gedmo\Versioned]
    private string $catalogUrl = "";

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    #[Assert\Url]
    #[Gedmo\Versioned]
    private ?string $homepage = null;

    #[ORM\Column(type: "boolean")]
    #[Gedmo\Versioned]
    private bool $hasFreeShipping = false;

    #[ORM\Column(type: "boolean")]
    #[Gedmo\Versioned]
    private bool $hasDiscount = false;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $comment = null;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    #[Gedmo\Versioned]
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

    public function getHomepage(): ?string
    {
        return $this->homepage;
    }

    public function setHomepage(?string $homepage): self
    {
        $this->homepage = $homepage;
        return $this;
    }
}
