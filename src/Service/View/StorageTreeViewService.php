<?php
declare(strict_types=1);

namespace App\Service\View;

use App\Entity\DoctrineEntity\Storage\Box;
use App\Entity\DoctrineEntity\Storage\Rack;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use Doctrine\Common\Collections\Collection;
use Doctrine\Persistence\Proxy;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @implements TreeViewServiceInterface<Rack|Box>
 */
class StorageTreeViewService implements TreeViewServiceInterface
{
    use DefaultTreeViewTrait;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Security $security,
    ) {
    }

    public function getNodeIcon(?object $node = null): ?string
    {
        if ($node instanceof Box) {
            return "box";
        } else {
            return "rack";
        }
    }

    public function isIconStacked(?object $node = null): bool
    {
        return false;
    }

    public function getNodeLabel(object $node): string
    {
        if ($node instanceof Box) {
            return "{$node->getName()} ({$node->getRows()} × {$node->getCols()})";
        } else {
            return $node->getName();
        }
    }

    public function getNodeUrl(object $node): string
    {
        if ($node instanceof Box) {
            return $this->urlGenerator->generate("app_storage_view_box", ["box" => $node->getId()]);
        } else {
            return $this->urlGenerator->generate("app_storage_view_rack", ["rack" => $node->getId()]);
        }
    }

    public function getNodeTools(object $node): ?Toolbox
    {
        if ($node instanceof Box) {
            return new Toolbox([
                new EditTool(
                    path: $this->urlGenerator->generate("app_storage_edit_box", ["box" => $node->getId()]),
                    icon: "box",
                    enabled: $this->security->isGranted("edit", $node),
                    tooltip: "Edit Box",
                    iconStack: "edit"
                ),
            ]);
        } else {
            return new Toolbox([
                new EditTool(
                    path: $this->urlGenerator->generate("app_storage_edit_rack", ["rack" => $node->getId()]),
                    icon: "rack",
                    enabled: $this->security->isGranted("edit", $node),
                    tooltip: "Edit Rack",
                    iconStack: "edit"
                ),
            ]);
        }
    }

    public function getPostNodeComponent(object $node): ?array
    {
        return null;
    }

    public function getPreChildComponent(object $node): ?array
    {
        return null;
    }

    public function getPostChildComponent(object $node): ?array
    {
        return null;
    }

    public function isIterable(?object $node = null): bool
    {
        if ($node instanceof Box) {
            return false;
        } else {
            return $node->getChildren()->count() > 0 || $node->getBoxes()->count() > 0;
        }
    }

    public function getTree(object $node): array
    {
        if ($node instanceof Box) {
            return [];
        } else {
            $racks = $node->getChildren()->toArray();
            $boxes = $node->getBoxes()->toArray();
            return [... $racks, ... $boxes];
        }
    }
}