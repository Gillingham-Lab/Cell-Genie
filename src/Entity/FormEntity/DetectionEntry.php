<?php
declare(strict_types=1);

namespace App\Entity\FormEntity;

use Symfony\Component\Validator\Constraints as Assert;

class DetectionEntry
{
    #[Assert\Length(min: 2, max: 20)]
    #[Assert\NotBlank]
    private ?string $method = null;

    #[Assert\NotBlank]
    private ?bool $isDetectable = null;

    #[Assert\Length(max: 255)]
    private ?string $comment = null;

    public function __toString(): string
    {
        return $this->method ?? "unknown";
    }

    public function __serialize(): array
    {
        return [
            "method" => $this->method,
            "isDetectable" => $this->isDetectable,
            "comment" => $this->comment,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->method = $data["method"] ?? "";
        $this->isDetectable = $data["isDetectable"] ?? false;
        $this->comment = $data["comment"] ?? "";
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): self
    {
        $this->method = $method;
        return $this;
    }

    public function getIsDetectable(): ?bool
    {
        return $this->isDetectable;
    }

    public function setIsDetectable(?bool $isDetectable): self
    {
        $this->isDetectable = $isDetectable;
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
}