<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProteinRepository;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=ProteinRepository::class)
 */
class Protein
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=10)
     */
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 1,
        max: 10,
        minMessage: "Must be at least {{ min }} character long.",
        maxMessage: "Only up to {{ max }} characters allowed.",
    )]
    private $shortName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Assert\NotBlank]
    private $longName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    #[Assert\Url]
    private $proteinAtlasUri;

    #[Pure]
    public function __toString(): string
    {
        return $this->getShortName() ?? "unknown";
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProteinAtlasUri(): ?string
    {
        return $this->proteinAtlasUri;
    }

    public function setProteinAtlasUri(?string $proteinAtlasUri): self
    {
        $this->proteinAtlasUri = $proteinAtlasUri;

        return $this;
    }
}
