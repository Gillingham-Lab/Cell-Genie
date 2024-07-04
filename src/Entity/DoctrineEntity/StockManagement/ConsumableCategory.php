<?php
declare(strict_types=1);

namespace App\Entity\DoctrineEntity\StockManagement;

use App\Entity\Interface\PrivacyAwareInterface;
use App\Entity\Traits\CommentTrait;
use App\Entity\Traits\Fields\IdTrait;
use App\Entity\Traits\Fields\LongNameTrait;
use App\Entity\Traits\Fields\ParentChildTrait;
use App\Entity\Traits\Privacy\PrivacyAwareTrait;
use App\Repository\StockKeeping\ConsumableCategoryRepository;
use App\Validator\Constraint\NotLooped;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Loggable;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ConsumableCategoryRepository::class)]
#[Loggable]
#[NotLooped("parent", "children")]
class ConsumableCategory implements PrivacyAwareInterface
{
    use IdTrait;
    use LongNameTrait;
    use ParentChildTrait;
    use PrivacyAwareTrait;
    use CommentTrait;

    #[ORM\OneToMany(mappedBy: "category", targetEntity: Consumable::class, cascade: ["persist", "remove"], fetch: "EAGER")]
    #[ORM\OrderBy(["longName" => "ASC"])]
    #[Assert\Valid]
    private Collection $consumables;

    #[ORM\Column]
    private bool $showUnits;

    #[ORM\Column]
    #[Assert\Range(min: 1)]
    private int $idealStock = 1;

    #[ORM\Column]
    #[Assert\Range(min: 1)]
    private int $orderLimit = 1;

    #[ORM\Column]
    #[Assert\Range(min: 0)]
    private int $criticalLimit = 0;

    public function __toString(): string
    {
        return $this->longName;
    }

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->consumables = new ArrayCollection();
    }

    public function getConsumables(): Collection
    {
        return $this->consumables;
    }

    public function addConsumable(Consumable $consumable): self
    {
        if ($this->consumables->contains($consumable)) {
            $this->consumables->add($consumable);
            $consumable->setCategory($this);
        }
        return $this;
    }

    public function removeConsumable(Consumable $consumable): self
    {
        if ($this->consumables->contains($consumable)) {
            $this->consumables->remove($consumable);
            $consumable->setCategory(null);
        }
        return $this;
    }

    public function isShowUnits(): bool
    {
        return $this->showUnits;
    }

    public function setShowUnits(bool $showUnits): self
    {
        $this->showUnits = $showUnits;
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

    public function getCurrentStock(): int
    {
        $stock = 0;
        /** @var ConsumableCommons $consumable */
        foreach ($this->consumables as $consumable) {
            $stock += $consumable->getCurrentStock();
        }

        return $stock;
    }
}