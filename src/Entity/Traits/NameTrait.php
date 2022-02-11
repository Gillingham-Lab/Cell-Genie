<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait NameTrait
{
    #[ORM\Column(type: "string", length: 20)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 1,
        max: 20,
        minMessage: "Must be at least {{ min }} character long.",
        maxMessage: "Only up to {{ max }} characters allowed.",
    )]
    private ?string $shortName;

    #[ORM\Column(type: "string", length: 255)]
    #[Assert\Length(max: 250)]
    private ?string $longName = "";

    public function __toString(): string
    {
        return $this->getShortName() ?? "unknown";
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setShortName(string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getLongName(): ?string
    {
        return $this->longName;
    }

    public function setLongName(string $longName): self
    {
        $this->longName = $longName;

        return $this;
    }
}