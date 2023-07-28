<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\CommentTrait;
use App\Entity\Traits\Fields\IdTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[Gedmo\Loggable]
class SequenceAnnotation implements \JsonSerializable
{
    use IdTrait;
    use CommentTrait;

    #[ORM\Column(type: "string", length: 50, nullable: false)]
    #[Gedmo\Versioned]
    private ?string $annotationLabel;

    #[ORM\Column(type: "string", length: 50, nullable: false)]
    #[Gedmo\Versioned]
    private ?string $annotationType;

    #[ORM\Column(type: "string", length: 50, nullable: true)]
    #[Gedmo\Versioned]
    private ?string $color;

    #[ORM\Column(type: "boolean", options: ["default" => false])]
    private bool $isComplement = false;

    #[ORM\Column(type: "integer", nullable: false)]
    #[Assert\Range(min: 1)]
    #[Assert\Positive]
    private ?int $annotationStart;

    #[ORM\Column(type: "integer", nullable: false)]
    #[Assert\Range(min: 1)]
    #[Assert\Positive]
    private ?int $annotationEnd;

    #[ORM\Column(type: "array", nullable: true)]
    private array $annotations = [];

    public function __toString(): string
    {
        return sprintf("%s<%s%d..%d>", $this->getAnnotationLabel(), $this->isComplement() ? "c" : "", $this->getAnnotationStart(), $this->getAnnotationEnd());
    }

    public function jsonSerialize(): array
    {
        return [
            "id" => $this->getId()->toBase32(),
            "type" => $this->annotationType,
            "label" => $this->annotationLabel,
            "start" => $this->annotationStart,
            "end" => $this->annotationEnd,
            "complement" => $this->isComplement,
            "color" => $this->color,
            "annotations" => $this->annotations,
        ];
    }

    public function getAnnotationLabel(): ?string
    {
        return $this->annotationLabel;
    }

    public function setAnnotationLabel(?string $annotationLabel): self
    {
        $this->annotationLabel = $annotationLabel;
        return $this;
    }

    public function getAnnotationType(): ?string
    {
        return $this->annotationType;
    }

    public function setAnnotationType(?string $annotationType): self
    {
        $this->annotationType = $annotationType;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function isComplement(): bool
    {
        return $this->isComplement;
    }

    public function setIsComplement(bool $isComplement): self
    {
        $this->isComplement = $isComplement;
        return $this;
    }

    public function getAnnotationStart(): ?int
    {
        return $this->annotationStart;
    }

    public function setAnnotationStart(?int $annotationStart): self
    {
        $this->annotationStart = $annotationStart;
        return $this;
    }

    public function getAnnotationEnd(): ?int
    {
        return $this->annotationEnd;
    }

    public function setAnnotationEnd(?int $annotationEnd): self
    {
        $this->annotationEnd = $annotationEnd;
        return $this;
    }

    public function getAnnotations(): array
    {
        return $this->annotations;
    }

    public function setAnnotations(array $annotations): self
    {
        $this->annotations = $annotations;
        return $this;
    }
}