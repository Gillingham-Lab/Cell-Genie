<?php
declare(strict_types=1);

namespace App\Entity\Traits\Fields;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

trait DescriptionTrait
{
    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    #[Assert\NotBlank]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
}
