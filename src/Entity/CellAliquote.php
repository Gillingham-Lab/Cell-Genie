<?php
declare(strict_types=1);

namespace App\Entity;

use App\Entity\Traits\HasBoxTrait;
use App\Repository\CellAliquoteRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CellAliquoteRepository::class)]
#[Gedmo\Loggable]
class CellAliquote
{
    use HasBoxTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = 0;

    #[ORM\Column(type: "datetime", nullable: true)]
    #[Assert\GreaterThanOrEqual("1970-01-01 00:00:00")]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $aliquoted_on;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?User $aliquoted_by = null;

    #[ORM\Column(type: "string", length: 30)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 30)]
    #[Gedmo\Versioned]
    private ?string $vialColor = "grey";

    #[ORM\Column(type: "integer")]
    #[Assert\Range(min: 1)]
    #[Gedmo\Versioned]
    private ?int $vials = 1;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Gedmo\Versioned]
    private ?int $passage = null;

    #[ORM\Column(type: "string", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $passageDetail = null;

    #[ORM\Column(type: "integer")]
    #[Gedmo\Versioned]
    private ?int $cellCount = 0;

    #[ORM\Column(type: "datetime", nullable: true)]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $mycoplasmaTestedOn = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Gedmo\Versioned]
    private ?User $mycoplasmaTestedBy = null;

    #[ORM\Column(type: "string", nullable: false, options: ["default" => "unknown"])]
    #[Gedmo\Versioned]
    private ?string $mycoplasmaResult = "unknown";

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $mycoplasma = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $typing = null;

    #[ORM\Column(type: "text", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $history = null;

    #[ORM\ManyToOne(targetEntity: Cell::class, inversedBy: "cellAliquotes")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[Gedmo\Versioned]
    #[Assert\NotBlank]
    private ?Cell $cell = null;

    #[ORM\Column(type: "string", length: 255, nullable: true)]
    #[Assert\Length(max: 250)]
    #[Gedmo\Versioned]
    private ?string $cryoMedium = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAliquotedOn(): ?DateTimeInterface
    {
        return $this->aliquoted_on;
    }

    public function setAliquotedOn(?DateTimeInterface $aliquoted_on): self
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

    public function getPassageDetail(): ?string
    {
        return $this->passageDetail;
    }

    public function setPassageDetail(?string $passageDetail): self
    {
        $this->passageDetail = $passageDetail;
        return $this;
    }

    public function getMycoplasmaTestedOn(): ?DateTimeInterface
    {
        return $this->mycoplasmaTestedOn;
    }

    public function setMycoplasmaTestedOn(?DateTimeInterface $mycoplasmaTestedOn): self
    {
        $this->mycoplasmaTestedOn = $mycoplasmaTestedOn;
        return $this;
    }

    public function getMycoplasmaTestedBy(): ?User
    {
        return $this->mycoplasmaTestedBy;
    }

    public function setMycoplasmaTestedBy(?User $mycoplasmaTestedBy): self
    {
        $this->mycoplasmaTestedBy = $mycoplasmaTestedBy;
        return $this;
    }

    public function getMycoplasmaResult(): ?string
    {
        return $this->mycoplasmaResult;
    }

    public function setMycoplasmaResult(?string $mycoplasmaResult): self
    {
        $this->mycoplasmaResult = $mycoplasmaResult;
        return $this;
    }
}
