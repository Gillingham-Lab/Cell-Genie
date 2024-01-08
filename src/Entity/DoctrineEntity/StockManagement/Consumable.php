<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\StockManagement;

use App\Entity\Interface\PrivacyAwareInterface;
use App\Entity\Traits\CommentTrait;
use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\Fields\LongNameTrait;
use App\Entity\Traits\Privacy\PrivacyAwareTrait;
use App\Entity\Traits\VendorTrait;
use App\Genie\Enums\Availability;
use App\Repository\StockKeeping\ConsumableRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Loggable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConsumableRepository::class)]
#[Loggable]
class Consumable implements PrivacyAwareInterface
{
    use IdTrait;
    use PrivacyAwareTrait;
    use LongNameTrait;
    use VendorTrait;
    use ConsumableCommons;
    use CommentTrait;

    #[ORM\ManyToOne(targetEntity: ConsumableCategory::class, inversedBy: "consumables")]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    private ?ConsumableCategory $category = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?string $productNumber;

    #[ORM\Column]
    private bool $consumePackage = false;

    #[ORM\Column]
    #[Assert\Range(min: 1)]
    private int $idealStock = 1;

    #[ORM\Column]
    #[Assert\Range(min: 1)]
    private int $orderLimit = 1;

    #[ORM\Column]
    #[Assert\Range(min: 0)]
    private int $criticalLimit = 0;

    #[ORM\Column]
    private ?string $expectedDeliveryTime;

    #[ORM\OneToMany(mappedBy: "consumable", targetEntity: ConsumableLot::class, cascade: ["persist", "remove"], fetch: "EAGER", orphanRemoval: true)]
    #[ORM\OrderBy(["boughtOn" => "ASC"])]
    #[Assert\Valid]
    private Collection $lots;

    public function __construct()
    {
        $this->lots = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->longName;
    }

    public function getCategory(): ?ConsumableCategory
    {
        return $this->category;
    }

    public function setCategory(?ConsumableCategory $category): self
    {
        $oldCategory = $this->category;
        $this->category = $category;

        if ($category === null) {
            if ($oldCategory !== null) {
                $this->category->removeConsumable($this);
            }
        } else {
            if (!$category->getConsumables()->contains($this)) {
                $category->addConsumable($this);
            }
        }

        return $this;
    }

    public function getProductNumber(): ?string
    {
        return $this->productNumber;
    }

    public function setProductNumber(?string $productNumber): self
    {
        $this->productNumber = $productNumber;
        return $this;
    }

    public function isConsumePackage(): bool
    {
        return $this->consumePackage;
    }

    public function setConsumePackage(bool $consumePackage): self
    {
        $this->consumePackage = $consumePackage;
        return $this;
    }

    public function getOrderLimit(): int
    {
        return $this->orderLimit;
    }

    public function setOrderLimit(int $orderLimit): self
    {
        $this->orderLimit = $orderLimit;
        return $this;
    }

    public function getCriticalLimit(): int
    {
        return $this->criticalLimit;
    }

    public function setCriticalLimit(int $criticalLimit): self
    {
        $this->criticalLimit = $criticalLimit;
        return $this;
    }

    public function getExpectedDeliveryTime(): ?string
    {
        return $this->expectedDeliveryTime;
    }

    public function setExpectedDeliveryTime(?string $expectedDeliveryTime): self
    {
        $this->expectedDeliveryTime = $expectedDeliveryTime;
        return $this;
    }

    public function getLots(): Collection
    {
        return $this->lots;
    }

    public function createLot(): ConsumableLot
    {
        $lot = new ConsumableLot();
        $lot
            ->setLocation($this->getLocation())
            ->setUnitSize($this->getUnitSize())
            ->setNumberOfUnits($this->getNumberOfUnits())
            ->setPricePerPackage($this->getPricePerPackage())
        ;

        return $lot;
    }

    public function addLot(ConsumableLot $lot): self
    {
        if (!$this->lots->contains($lot)) {
            $this->lots->add($lot);
            $lot->setConsumable($this);
        }
        return $this;
    }

    public function removeLot(ConsumableLot $lot): self
    {
        if ($this->lots->contains($lot)) {
            $this->lots->remove($lot);
            $lot->setConsumable(null);
        }
        return $this;
    }

    public function getIdealStock(): int
    {
        return $this->idealStock;
    }

    public function setIdealStock(int $idealStock): self
    {
        $this->idealStock = $idealStock;
        return $this;
    }

    public function getCurrentStock(): int
    {
        $availableLots = $this->lots->filter(fn (ConsumableLot $lot) => $lot->getAvailability() === Availability::Available);
        return $this->sumLots($availableLots);
    }

    public function getOrderedStock(): int
    {
        $orderedLots = $this->lots->filter(fn (ConsumableLot $lot) => $lot->getAvailability() === Availability::Ordered or  $lot->getAvailability() === Availability::InPreparation);
        return $this->sumLots($orderedLots);
    }

    /**
     * @param Collection<int, ConsumableLot> $lots
     * @return int
     */
    private function sumLots(Collection $lots): int
    {
        if ($this->consumePackage) {
            $method = fn (ConsumableLot $lot): int => $lot->getNumberOfUnits() - $lot->getUnitsConsumed();
        } else {
            $method = fn (ConsumableLot $lot): int => $lot->getTotalAvailablePieces();
        }

        $stock = 0;
        foreach ($lots as $lot) {
            $stock += $method($lot);
        }

        return $stock;
    }
}