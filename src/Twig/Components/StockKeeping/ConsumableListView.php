<?php
declare(strict_types=1);

namespace App\Twig\Components\StockKeeping;

use App\Entity\DoctrineEntity\StockManagement\Consumable;
use App\Entity\DoctrineEntity\StockManagement\ConsumableCategory;
use App\Twig\Components\ProgressBar;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PreMount;

/**
 * @phpstan-import-type ProgressBarColor from ProgressBar
 * @phpstan-import-type SubProgressBarProps from ProgressBar
 */
#[AsLiveComponent]
class ConsumableListView
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?ConsumableCategory $category = null;

    /**
     * @var Consumable[] $consumables
     */
    #[LiveProp(useSerializerForHydration: true, serializationContext: [
        "groups" => ["twig", "component"],
    ])]
    public array $allConsumables = [];

    #[LiveProp]
    public ?Consumable $currentConsumable = null;

    #[LiveProp]
    public ?ConsumableCategory $currentCategory = null;

    #[LiveProp]
    public bool $includingChildren = false;

    #[PreMount]
    public function preMount(array $props): array
    {
        if (isset($props["allConsumables"]) and $props["allConsumables"] instanceof Collection) {
            $props["allConsumables"] = $props["allConsumables"]->toArray();
        }

        return $props;
    }

    public function getConsumables(?ConsumableCategory $category = null): iterable
    {
        if ($this->category === null) {
            foreach ($this->allConsumables as $consumable) {
                yield $consumable;
            }
        } else {
            if (!$this->includingChildren) {
                yield from $this->category->getConsumables();
            } else {
                if (!$category) {
                    $category = $this->category;
                }

                yield from $category->getConsumables();

                foreach ($category->getChildren() as $child) {
                    yield from $this->getConsumables($child);
                }
            }
        }
    }

    /**
     * @param Consumable $consumable
     * @return array{current: numeric, max: numeric, color: ProgressBarColor, subBars: SubProgressBarProps[]}
     */
    public function getProgressBar(Consumable $consumable): array
    {
        $barColor = "success";

        if ($consumable->getCurrentStock() < $consumable->getCriticalLimit()) {
            $barColor = "danger";
        } elseif ($consumable->getCurrentStock() < $consumable->getOrderLimit()) {
            $barColor = "warning";
        }

        $additionalBars = [];
        if ($consumable->getOrderedStock() > 0) {
            $additionalBars[] = [
                "current" => $consumable->getOrderedStock(),
                "striped" => true,
                "color" => "optional",
                "showNumbers" => true,
            ];
        }

        return [
            "current" => $consumable->getCurrentStock(),
            "max" => $consumable->getIdealStock(),
            "color" => $barColor,
            "subBars" => $additionalBars,
        ];
    }
}