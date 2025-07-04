<?php
declare(strict_types=1);

namespace App\Service\View;

use App\Entity\DoctrineEntity\StockManagement\ConsumableCategory;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use App\Twig\Components\ProgressBar;
use App\Twig\Components\StockKeeping\ConsumableListView;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @implements TreeViewServiceInterface<ConsumableCategory>
 */
class ConsumableTreeViewService implements TreeViewServiceInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private readonly Security $security,
    ) {
    }

    public function getNodeIcon(): string
    {
        return "consumable";
    }

    public function getNodeLabel(object $node): string
    {
        return $node->getLongName();
    }

    public function getNodeUrl(object $node): string
    {
        return $this->urlGenerator->generate("app_consumables_category_view", [
            "category" => $node->getId(),
        ]);
    }

    public function getNodeTools(object $node): ?Toolbox
    {
        $tools = [];

        if ($this->security->isGranted("edit", $node)) {
            $tools[] = new EditTool(
                path: $this->urlGenerator->generate("app_consumables_category_edit", ["category" => $node->getId()]),
                tooltip: "Edit category",
            );
        }

        if (count($tools) > 0) {
            return new Toolbox($tools);
        } else {
            return null;
        }
    }

    public function getPostNodeComponent(object $node): ?array
    {
        if ($node->isShowUnits() === false) {
            return null;
        }

        $barColor = "success";
        //$stockFilling = $node->getCurrentStock() / $node->getIdealStock();
        //$stockFilling = $stockFilling > 1 ? 1 : $stockFilling;

        if ($node->getCurrentStock() < $node->getCriticalLimit()) {
            $barColor = "danger";
        } elseif ($node->getCurrentStock() < $node->getOrderLimit()) {
            $barColor = "warning";
        }

        return [
            ProgressBar::class, [
                "current" => $node->getCurrentStock(),
                "max" => $node->getIdealStock(),
                "showNumbers" => true,
                "color" => $barColor,
                "minWidth" => 10,
            ]
        ];
    }

    public function getPreChildComponent(object $node): ?array
    {
        return [
            ConsumableListView::class, [
                "loading" => "lazy",
                "category" => $node,
                "includingChildren" => true,
            ]
        ];
    }

    public function getPostChildComponent(object $node): ?array
    {
        return null;
    }
}