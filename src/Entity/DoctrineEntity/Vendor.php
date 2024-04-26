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
    #[Assert\NotNull(message: "You must provide a catalog url.")]
    private ?string $catalogUrl = null;

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

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCatalogUrl(): ?string
    {
        return $this->catalogUrl;
    }

    public function setCatalogUrl(?string $catalogUrl): static
    {
        $this->catalogUrl = $catalogUrl;
        return $this;
    }

    public function getHasFreeShipping(): ?bool
    {
        return $this->hasFreeShipping;
    }

    public function setHasFreeShipping(bool $hasFreeShipping): static
    {
        $this->hasFreeShipping = $hasFreeShipping;

        return $this;
    }

    public function getHasDiscount(): ?bool
    {
        return $this->hasDiscount;
    }

    public function setHasDiscount(bool $hasDiscount): static
    {
        $this->hasDiscount = $hasDiscount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getIsPreferred(): ?bool
    {
        return $this->isPreferred;
    }

    public function setIsPreferred(bool $isPreferred): static
    {
        $this->isPreferred = $isPreferred;

        return $this;
    }

    public function getHomepage(): ?string
    {
        return $this->homepage;
    }

    public function setHomepage(?string $homepage): static
    {
        $this->homepage = $homepage;
        return $this;
    }

    public function getProductUrl(?string $productNumber = null): string
    {
        if (!$productNumber) {
            return str_replace("{pn}", "", $this->catalogUrl);
        } else {
            if (strpos($this->catalogUrl, "{pn}") === false) {
                if (str_ends_with($this->catalogUrl, "#")) {
                    return $this->catalogUrl;
                } else {
                    return $this->catalogUrl . $productNumber;
                }
            } else {
                return str_replace("{pn}", $productNumber, $this->catalogUrl);
            }
        }
    }
}
