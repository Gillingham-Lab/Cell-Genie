<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\CellAliquoteRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CellAliquoteRepository::class)
 */
class CellAliquote
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    private DateTimeInterface $aliquoted_on;

    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private ?User $aliquoted_by;

    /**
     * @ORM\ManyToOne(targetEntity=Box::class, inversedBy="cellAliquotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private Box $box;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private string $vialColor;

    /**
     * @ORM\Column(type="integer")
     */
    private int $vials;

    /**
     * @ORM\Column(type="integer")
     */
    private int $passage;

    /**
     * @ORM\Column(type="integer")
     */
    private int $cellCount;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $mycoplasma = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $typing = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $history = null;

    /**
     * @ORM\ManyToOne(targetEntity=Cell::class, inversedBy="cellAliquotes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cell;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $cryoMedium;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAliquotedOn(): ?DateTimeInterface
    {
        return $this->aliquoted_on;
    }

    public function setAliquotedOn(DateTimeInterface $aliquoted_on): self
    {
        $this->aliquoted_on = $aliquoted_on;

        return $this;
    }

    public function getAliquotedBy(): ?User
    {
        return $this->aliquoted_by;
    }

    public function setAliquotedBy(?User $aliquoted_by): self
    {
        $this->aliquoted_by = $aliquoted_by;

        return $this;
    }

    public function getBox(): ?Box
    {
        return $this->box;
    }

    public function setBox(?Box $box): self
    {
        $this->box = $box;

        return $this;
    }

    public function getVialColor(): ?string
    {
        return $this->vialColor;
    }

    public function setVialColor(string $vialColor): self
    {
        $this->vialColor = $vialColor;

        return $this;
    }

    public function getVials(): ?int
    {
        return $this->vials;
    }

    public function setVials(int $vials): self
    {
        $this->vials = $vials;

        return $this;
    }

    public function getPassage(): ?int
    {
        return $this->passage;
    }

    public function setPassage(int $passage): self
    {
        $this->passage = $passage;

        return $this;
    }

    public function getCellCount(): ?int
    {
        return $this->cellCount;
    }

    public function setCellCount(int $cellCount): self
    {
        $this->cellCount = $cellCount;

        return $this;
    }

    public function getMycoplasma(): ?string
    {
        return $this->mycoplasma;
    }

    public function setMycoplasma(?string $mycoplasma): self
    {
        $this->mycoplasma = $mycoplasma;

        return $this;
    }

    public function getTyping(): ?string
    {
        return $this->typing;
    }

    public function setTyping(?string $typing): self
    {
        $this->typing = $typing;

        return $this;
    }

    public function getHistory(): ?string
    {
        return $this->history;
    }

    public function setHistory(?string $history): self
    {
        $this->history = $history;

        return $this;
    }

    public function getCell(): ?Cell
    {
        return $this->cell;
    }

    public function setCell(?Cell $cell): self
    {
        $this->cell = $cell;

        return $this;
    }

    public function getCryoMedium(): ?string
    {
        return $this->cryoMedium;
    }

    public function setCryoMedium(?string $cryoMedium): self
    {
        $this->cryoMedium = $cryoMedium;

        return $this;
    }
}
