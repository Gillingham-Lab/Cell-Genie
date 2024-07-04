<?php
declare(strict_types=1);

namespace App\Twig\Components\Live;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Table\ColorColumn;
use App\Entity\Table\Column;
use App\Entity\Table\ComponentColumn;
use App\Entity\Table\ProgressColumn;
use App\Entity\Table\Table;
use App\Entity\Table\ToggleColumn;
use App\Entity\Table\ToolboxColumn;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Tool;
use App\Entity\Toolbox\Toolbox;
use App\Entity\Toolbox\TrashTool;
use App\Entity\Toolbox\ViewTool;
use App\Twig\Components\Date;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class CellAliquotTable extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?Cell $cell = null;
    #[LiveProp]
    public ?CellAliquot $currentAliquot = null;

    public function getTable(): array
    {
        if (!$this->cell) {
            return [];
        }

        $table = new Table(
            data: $this->cell->getCellAliquots()->filter(fn(CellAliquot $aliquot) => $this->isGranted("view", $aliquot)),
            columns: [
                new ToolboxColumn("", fn (CellAliquot $aliquot) => new Toolbox([
                    new ViewTool(
                        path: $aliquot->getCell()->getCellNumber()
                            ? $this->generateUrl("app_cell_aliquot_view_number", ["cellNumber" => $aliquot->getCell()->getCellNumber(), "aliquotId" => $aliquot->getId()])
                            : $this->generateUrl("app_cell_view", ["cellId" => $aliquot->getCell()->getId(), "aliquotId" => $aliquot->getId()])
                        ,
                        enabled: $this->isGranted("view", $aliquot),
                        tooltip: "View aliquot",
                    ),
                    new EditTool(
                        path: $this->generateUrl("app_cell_aliquot_edit", ["cell" => $aliquot->getCell()->getCellNumber(), "cellAliquot" => $aliquot->getId()]),
                        enabled: $this->isGranted("edit", $aliquot),
                        tooltip: "Edit aliquot"
                    ),
                    new Tool(
                        path: $this->generateUrl("app_cell_consume_aliquot", ["aliquotId" => $aliquot->getId()]),
                        icon: "minus",
                        buttonClass: "btn-secondary",
                        enabled:   $this->isGranted("consume", $aliquot),
                        tooltip: "Consume aliquot",
                    ),
                    new TrashTool(
                        path: $this->generateUrl("app_cell_trash_aliquot", ["aliquotId" => $aliquot->getId()]),
                        enabled: $this->isGranted("trash", $aliquot),
                        tooltip: "Trash aliquot",
                    ),
                ])),
                new Column("ID", function (CellAliquot $aliquot) {
                    return $aliquot->getId();
                }),
                new Column("Nr", function (CellAliquot $aliquot) {
                    if ($aliquot->getAliquotName()) {
                        return $aliquot->getAliquotName();
                    } else {
                        return $aliquot->getId();
                    }
                }),
                new Column("Passage", fn (CellAliquot $aliquot) => $aliquot->getPassage()),
                new ColorColumn("Myc free", fn(CellAliquot $aliquot) => match ($aliquot->getMycoplasmaResult()) {"negative" => "green", "positive" => "red", default => "#DDDDDD"}),
                new Column("Aliquoted by", fn (CellAliquot $aliquot) => $aliquot->getAliquotedBy() ?? "unknown"),
                new ComponentColumn("Aliquoted on", fn (CellAliquot $aliquot) => [Date::class, ["dateTime" => $aliquot->getAliquotedOn()]]),
                new ColorColumn("Vial", fn (CellAliquot $aliquot) => $aliquot->getVialColor()),
                new Column("Box", fn (CellAliquot $aliquot) => $aliquot->getBox()->getName()),
                new Column("Position", fn (CellAliquot $aliquot) => $aliquot->getBoxCoordinate() ?? "?"),
                new ProgressColumn("Vials", fn (CellAliquot $aliquot) => [
                    $aliquot->getVials(),
                    $aliquot->getMaxVials()??$aliquot->getVials(),
                ], showNumbers: true),
            ],
            isActive: fn (CellAliquot $aliquot) => $aliquot === $this->currentAliquot,
        );

        return $table->toArray();
    }
}