<?php
declare(strict_types=1);

namespace App\Service\View;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Toolbox\AddTool;
use App\Entity\Toolbox\ClipwareTool;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use App\Entity\Toolbox\TrashTool;
use App\Entity\Toolbox\ViewTool;
use App\Form\UserEntityType;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @implements ListViewServiceInterface<Cell>
 */
class CellListViewService implements ListViewServiceInterface
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Security $security,
    ) {
    }

    public function sortItems(array $items): array
    {
        usort($items, function (Cell $a, Cell $b): int {
            if ($a->getGroup() !== $b->getGroup()) {
                /** @var User $user */
                $user = $this->security->getUser();

                if ($a->getGroup() === $user->getGroup()) {
                    return -1;
                } elseif ($b->getGroup() !== $user->getGroup()) {
                    return 1;
                } else {
                    return $a->getGroup()->getShortName() <=> $b->getGroup()->getShortName();
                }
            } else {
                return $a->getCellNumber() <=> $b->getCellNumber();
            }
        });

        return $items;
    }

    public function getItemIcon(): ?string
    {
        return "cell";
    }

    public function getItemLabel(object $item): string
    {
        return (string)$item;
    }

    public function getItemUrl(object $item): string
    {
        return $this->urlGenerator->generate("app_cell_view_number", ["cellNumber" => $item->getCellNumber()]);
    }

    public function getItemTools(object $item): ?Toolbox
    {
        return new Toolbox([
            new ViewTool(
                path: $this->getItemUrl($item),
                icon: "cell",
                tooltip: "View cell",
                iconStack: "view",
            ),
            new ClipwareTool(
                clipboardText: sprintf("%s (RRID:%s)", $item->getName(), $item->getRrid() ?? "none"),
                icon: "cell",
                tooltip: "Copy citation",
                iconStack: "clipboard",
            ),
            new EditTool(
                path: $this->urlGenerator->generate("app_cell_edit", ["cell" => $item->getCellNumber()]),
                icon: "cell",
                tooltip: "Edit cell",
                iconStack: "edit",
            ),
            new AddTool(
                path: $this->urlGenerator->generate("app_cell_aliquot_add", ["cell" => $item->getCellNumber()]),
                icon: "cell",
                tooltip: "Add aliquot",
                iconStack: "add",
            )
        ]);
    }

    public function getPostItemComponent(object $item): ?array
    {
        return [null, $item->getGroup()->getShortName()];
    }

    public function isEmpty(object $item): bool
    {
        return $item->getCellAliquots()->count() === 0;
    }
}