<?php
declare(strict_types=1);

namespace App\Entity\Traits\Fields;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

trait TitleTrait
{
    #[ORM\Column(type: "string", length: 255)]
    #[Assert\Length(min: 5, max: 250)]
    #[Gedmo\Versioned]
    private ?string $title = "";

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }
}
