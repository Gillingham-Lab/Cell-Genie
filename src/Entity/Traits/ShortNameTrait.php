<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

trait ShortNameTrait
{
    #[ORM\Column(type: "string", length: 20)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 1,
        max: 20,
    )]
    #[Gedmo\Versioned]
    private ?string $shortName;

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
}
