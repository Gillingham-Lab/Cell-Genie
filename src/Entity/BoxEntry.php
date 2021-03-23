<?php

namespace App\Entity;

use App\Repository\BoxEntryRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BoxEntryRepository::class)
 */
class BoxEntry
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
    private $box;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $boxRow;

    /**
     * @ORM\Column(type="integer")
     */
    private $boxCol;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBox(): ?string
    {
        return $this->box;
    }

    public function setBox(string $box): self
    {
        $this->box = $box;

        return $this;
    }

    public function getBoxRow(): ?string
    {
        return $this->boxRow;
    }

    public function setBoxRow(string $boxRow): self
    {
        $this->boxRow = $boxRow;

        return $this;
    }

    public function getBoxCol(): ?int
    {
        return $this->boxCol;
    }

    public function setBoxCol(int $boxCol): self
    {
        $this->boxCol = $boxCol;

        return $this;
    }
}
