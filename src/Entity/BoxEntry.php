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
     * @ORM\ManyToOne(targetEntity=BoxEntry::class, inversedBy="entries")
     */
    private Box $box;

    /**
     * @ORM\Column(type="integer")
     */
    private int $boxRow;

    /**
     * @ORM\Column(type="integer")
     */
    private int $boxCol;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBox(): ?string
    {
        return $this->box;
    }

    public function setBox(Box $box): self
    {
        $this->box = $box;

        return $this;
    }

    public function getBoxRow(): ?int
    {
        return $this->boxRow;
    }

    public function setBoxRow(int $boxRow): self
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
