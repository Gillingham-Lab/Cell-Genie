<?php

namespace App\Entity;

use App\Repository\CultureFlaskRepository;
use Doctrine\ORM\Mapping as ORM;
use JetBrains\PhpStorm\Pure;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=CultureFlaskRepository::class)
 */
class CultureFlask
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    #[Assert\NotBlank]
    private $name;

    /**
     * @ORM\Column(type="smallint", options={"default": 1})
     * Assert\NotBlank
     */
    #[Assert\NotBlank]
    #[Assert\Range(
        notInRangeMessage: "Value must be between {{ min }} and {{ max }}.",
        min: 1,
        max: 100
    )]
    private int $rows = 1;

    /**
     * @ORM\Column(type="smallint")
     * Assert\Range(1, 100)
     */
    #[Assert\NotBlank]
    #[Assert\Range(
        notInRangeMessage: "Value must be between {{ min }} and {{ max }}.",
        min: 1,
        max: 100
    )]
    private int $cols = 1;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $comment = "";

    #[Pure]
    public function __toString(): string
    {
        return $this->getName() ?? "unknown";
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRows(): ?int
    {
        return $this->rows;
    }

    public function setRows(int $rows): self
    {
        $this->rows = $rows;

        return $this;
    }

    public function getCols(): ?int
    {
        return $this->cols;
    }

    public function setCols(int $cols): self
    {
        $this->cols = $cols;

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
