<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\StockManagement;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Traits\Fields\IdTrait;
use App\Genie\Enums\Availability;
use App\Repository\StockKeeping\ConsumableLotRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Loggable;
use Symfony\Component\Validator\Constraints as Assert;

#[Loggable]
#[ORM\Entity(repositoryClass: ConsumableLotRepository::class)]
class ConsumableLot
{
    use IdTrait;
    use ConsumableCommons;

    #[ORM\ManyToOne(targetEntity: Consumable::class, inversedBy: "lots")]
    #[ORM\JoinColumn(nullable: false, onDelete: "CASCADE")]
    #[Assert\NotBlank]
    private ?Consumable $consumable;

    #[ORM\Column(type: "string", enumType: Availability::class, options: ["default" => Availability::Available])]
    private ?Availability $availability;

    #[ORM\Column(type: "date")]
    #[Assert\NotBlank]
    private ?DateTimeInterface $boughtOn = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?DateTimeInterface $arrivedOn = null;

    #[ORM\Column(type: "date", nullable: true)]
    private ?DateTimeInterface $openedOn = null;

    #[ORM\Column]
    #[Assert\Range(min: 0)]
    private int $unitsConsumed = 0;

    #[ORM\Column]
    #[Assert\Range(min: 0)]
    private int $piecesConsumed = 0;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: "SET NULL")]
    #[Assert\NotBlank]
    private ?User $boughtBy = null;

    public function __toString(): string
    {
        return $this->getLotIdentifier();
    }

    public function getLotIdentifier(
    ): string {
        $lotDate = $this->boughtOn?->format("ymd");

        if ($this->getId()) {
            $idFragment = substr($this->getId()->toBase32(), -4);
        } else {
            $idFragment = "YYMMDD-????";
        }

        if ($lotDate) {
            return "{$lotDate}-{$idFragment}";
        } else {
            return "{$idFragment}";
        }
    }

    public function getConsumable(): Consumable
    {
        return $this->consumable;
    }

    public function setConsumable(?Consumable $consumable): self
    {
        $this->consumable = $consumable;
        return $this;
    }

    public function getAvailability(): ?Availability
    {
        return $this->availability;
    }

    public function setAvailability(?Availability $availability): self
    {
        $this->availability = $availability;
        return $this;
    }

    public function getBoughtOn(): ?DateTimeInterface
    {
        return $this->boughtOn;
    }

    public function setBoughtOn(?DateTimeInterface $boughtOn): self
    {
        $this->boughtOn = $boughtOn;
        return $this;
    }

    public function getArrivedOn(): ?DateTimeInterface
    {
        return $this->arrivedOn;
    }

    public function setArrivedOn(?DateTimeInterface $arrivedOn): self
    {
        $this->arrivedOn = $arrivedOn;
        return $this;
    }

    public function getOpenedOn(): ?DateTimeInterface
    {
        return $this->openedOn;
    }

    public function setOpenedOn(?DateTimeInterface $openedOn): self
    {
        $this->openedOn = $openedOn;
        return $this;
    }

    public function getBoughtBy(): ?User
    {
        return $this->boughtBy;
    }

    public function setBoughtBy(?User $boughtBy): self
    {
        $this->boughtBy = $boughtBy;
        return $this;
    }

    public function getUnitsConsumed(): int
    {
        return $this->unitsConsumed;
    }

    public function setUnitsConsumed(int $unitsConsumed): self
    {
        $this->unitsConsumed = $unitsConsumed;
        return $this;
    }

    public function consumeUnit(int $units = 1): void
    {
        $this->unitsConsumed = $this->unitsConsumed + $units;
    }

    public function getPiecesConsumed(): int
    {
        return $this->piecesConsumed;
    }

    public function setPiecesConsumed(int $piecesConsumed): self
    {
        $this->piecesConsumed = $piecesConsumed;
        return $this;
    }

    public function consumePiece(int $pieces = 1): void
    {
        $this->piecesConsumed = $this->piecesConsumed + $pieces;
    }

    public function isPristine(): bool
    {
        if ($this->getAvailability() === Availability::Available and $this->getUnitsConsumed() === 0 and $this->getPiecesConsumed() === 0) {
            return true;
        } else {
            return false;
        }
    }

    public function getTotalAmountOfPieces(): int
    {
        return $this->getNumberOfUnits() * $this->getUnitSize();
    }

    public function getTotalConsumedPieces(): int
    {
        return $this->getUnitsConsumed() * $this->getUnitSize() + $this->getPiecesConsumed();
    }

    public function getTotalAvailablePieces(): int
    {
        return $this->getTotalAmountOfPieces() - $this->getTotalConsumedPieces();
    }

    public function getFullness(): float
    {
        $totalPieces = $this->getTotalAmountOfPieces();
        $consumedPieces = $this->getTotalConsumedPieces();

        return 1 - ($consumedPieces / $totalPieces);
    }

    public function getSortValue(): float
    {
        $fullness = $this->getFullness();

        if ($fullness == 0) {
            return -1;
        } elseif ($fullness == 1) {
            return 0;
        } else {
            return $fullness;
        }
    }
}