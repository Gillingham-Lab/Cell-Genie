<?php
declare(strict_types=1);

namespace App\Entity\Traits\Fields;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

trait LongNameTrait
{
    #[ORM\Column(type: "string", length: 255)]
    #[Assert\Length(max: 250)]
    #[Gedmo\Versioned]
    private ?string $longName = "";

    public function getLongName(): ?string
    {
        return $this->longName;
    }

    public function setLongName(?string $longName): self
    {
        $this->longName = $longName;

        return $this;
    }
}