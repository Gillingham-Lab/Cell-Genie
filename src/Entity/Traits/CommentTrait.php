<?php
declare(strict_types=1);

namespace App\Entity\Traits;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;

trait CommentTrait
{
    #[Groups([
        "twig",
    ])]
    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $comment = null;

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        return $this;
    }
}