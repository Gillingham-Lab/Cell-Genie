<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\Cell;

use App\Entity\Traits\HasBoxTrait;
use App\Entity\User;
use App\Repository\Cell\CellAliquotRepository;
use App\Validator\Constraint\ValidBoxCoordinate;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CellAliquotRepository::class)]
#[ORM\Table("cell_aliquote")]
#[Gedmo\Loggable]
class CellAliquot implements \JsonSerializable
{
    use HasBoxTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = 0;

    #[ORM\Column(type: "datetime", nullable: true)]
    #[Gedmo\Versioned]
    private ?DateTimeInterface $aliquoted_on = null;

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
    #[Assert\Range(min: 0)]
    #[Gedmo\Versioned]
    private ?int $vials = 1;

    #[ORM\Column(type: "integer", nullable: true, options: ["default" => 0])]
    #[Assert\Ramge(min: 1)]
    #[Gedmo\Versioned]
    private ?int $maxVials = 1;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Gedmo\Versioned]
    private ?int $passage = null;

    #[ORM\Column(type: "string", nullable: true)]
    #[Gedmo\Versioned]
    private ?string $passageDetail = null;

    #[ORM\Column(type: "integer", nullable: true)]
    #[Gedmo\Versioned]
    private ?int $cellCount = null;

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

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'childAliquots')]
    #[ORM\JoinColumn(onDelete: "SET NULL")]
    private ?self $parentAliquot = null;

    #[ORM\OneToMany(mappedBy: 'parentAliquot', targetEntity: self::class)]
    private Collection $childAliquots;

    #[ORM\Column(type: "string", length: 10, nullable: true)]
    #[Assert\Length(max: 10)]
    #[ValidBoxCoordinate]
    private ?string $boxCoordinate = null;

    public function __construct()
    {
        $this->childAliquots = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->cell . " | p" . $this->passage . " (id {$this->id})";
    }

    public function jsonSerialize(): mixed
    {
        return [
            "vialColor" => $this->getVialColor(),
            "numberOfAliquots" => $this->getVials(),
            "maxNumberOfAliquots" => $this->getMaxVials(),
            "number" => $this->getId(),
            "passage" => $this->getPassage(),
            "mycoplasmaResult" => $this->getMycoplasmaResult(),
            "aliquotedOn" => $this->getAliquotedOn()->format("c"),
            "aliquotedBy" => $this->getAliquotedBy()->getFullName(),
            "cryoMedium" => $this->getCryoMedium(),
            "cellCount" => $this->getCellCount(),

            "cell" => [
                "id" => $this->getCell()->getId(),
                "number" => $this->getCell()->getCellNumber(),
                "name" => $this->getCell()->getName(),
            ]
        ];
    }

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

    public function getMaxVials(): ?int
    {
        return $this->maxVials ?? $this->vials;
    }

    public function setMaxVials(?int $maxVials): self
    {
        $this->maxVials = $maxVials;

        if ($this->vials === null) {
            $this->vials = $maxVials;
        }

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

    public function getParentAliquot(): ?self
    {
        return $this->parentAliquot;
    }

    public function setParentAliquot(?self $parentAliquot): self
    {
        $this->parentAliquot = $parentAliquot;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getChildAliquots(): Collection
    {
        return $this->childAliquots;
    }

    public function addChildAliquot(self $childAliquot): self
    {
        if (!$this->childAliquots->contains($childAliquot)) {
            $this->childAliquots[] = $childAliquot;
            $childAliquot->setParentAliquot($this);
        }

        return $this;
    }

    public function removeChildAliquot(self $childAliquot): self
    {
        if ($this->childAliquots->removeElement($childAliquot)) {
            // set the owning side to null (unless already changed)
            if ($childAliquot->getParentAliquot() === $this) {
                $childAliquot->setParentAliquot(null);
            }
        }

        return $this;
    }

    public function getBoxCoordinate(): ?string
    {
        return $this->boxCoordinate;
    }

    public function setBoxCoordinate(?string $boxCoordinate): self
    {
        $this->boxCoordinate = $boxCoordinate;
        return $this;
    }
}
