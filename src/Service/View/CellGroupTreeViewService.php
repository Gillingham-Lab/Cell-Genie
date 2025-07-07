<?php
declare(strict_types=1);

namespace App\Service\View;

use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use App\Entity\Toolbox\TrashTool;
use App\Security\Voter\Cell\CellGroupVoter;
use App\Twig\Components\Cell\CellGroupCount;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @implements TreeViewServiceInterface<CellGroup>
 */
class CellGroupTreeViewService implements TreeViewServiceInterface
{
    use DefaultTreeViewTrait;
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Security $security,
    ) {
    }

    public function getNodeIcon(?object $node = null): ?string
    {
        return "cell";
    }

    public function getNodeLabel(object $node): string
    {
        return $node->getName();
    }

    public function getNodeUrl(object $node): string
    {
        return $this->urlGenerator->generate("app_cells_group", ["cellGroup" => $node->getId()]);
    }

    public function getNodeTools(object $node): ?Toolbox
    {
        $tools = [];

        if ($this->security->isGranted(CellGroupVoter::ATTR_EDIT, $node)) {
            $tools[] = new EditTool(
                path: $this->urlGenerator->generate("app_cells_group_edit", ["cellGroup" => $node->getId()]),
                tooltip: "Edit cell group",
            );
        }

        if ($this->security->isGranted(CellGroupVoter::ATTR_REMOVE, $node)) {
            $tools[] = new TrashTool(
                path: $this->urlGenerator->generate("app_cells_group_remove", ["cellGroup" => $node->getId()]),
                tooltip: "Remove cell group",
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
        return [
            CellGroupCount::class, [
                "group" => $node,
            ],
        ];
    }

    public function getPreChildComponent(object $node): ?array
    {
        return null;
    }

    public function getPostChildComponent(object $node): ?array
    {
        return null;
    }
}