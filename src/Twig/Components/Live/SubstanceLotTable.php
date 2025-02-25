<?php
declare(strict_types=1);

namespace App\Twig\Components\Live;

use App\Entity\DoctrineEntity\Lot;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\Table\Column;
use App\Entity\Table\ComponentColumn;
use App\Entity\Table\ProgressColumn;
use App\Entity\Table\Table;
use App\Entity\Table\ToolboxColumn;
use App\Entity\Toolbox\ClipwareTool;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use App\Genie\Enums\Availability;
use App\Twig\Components\Date;
use App\Twig\Components\EntityReference;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * @phpstan-import-type ArrayTableShape from Table
 */
#[AsLiveComponent]
class SubstanceLotTable extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?Substance $substance;

    /**
     * @return Table<Lot>
     */
    public function getTable(): Table
    {
        $table = new Table(
            data: $this->substance->getLots(),
            columns: [
                new ToolboxColumn("", fn(Lot $lot) => new Toolbox([
                    new EditTool(
                        path: $this->generateUrl("app_substance_edit_lot", ["substance" => $this->substance->getUlid()->toRfc4122(), "lot" => $lot->getId()->toRfc4122()]),
                    ),
                    new ClipwareTool(
                        clipboardText: $this->substance->getCitation($lot),
                    )
                ])),
                new Column("Nr", fn(Lot $lot) => $lot->getNumber(), bold: true),
                new Column("Lot#", fn(Lot $lot) => $lot->getLotNumber()),
                new Column("Status", fn(Lot $lot) => $lot->getAvailability()->value),
                new ComponentColumn("Opened on", fn(Lot $lot) => [Date::class, ["dateTime" => $lot->getOpenedOn()]]),
                new Column("Bought by", fn(Lot $lot) => $lot->getBoughtBy()?->getFullName() ?? "??"),
                new ComponentColumn("Bought on", fn(Lot $lot) => [Date::class, ["dateTime" => $lot->getBoughtOn()]]),
                new ComponentColumn("Location", fn(Lot $lot) => [EntityReference::class, ["entity" => $lot->getBox()]]),
                new Column("Coordinate", fn(Lot $lot) => $lot->getBoxCoordinate()),
                new Column("Amount", fn(Lot $lot) => $lot->getAmount()),
                new Column("Concentration", fn(Lot $lot) => $lot->getPurity()),
                new ProgressColumn("Aliquots", fn(Lot $lot) => [$lot->getNumberOfAliquotes(), $lot->getMaxNumberOfAliquots()], showNumbers: true),
                new Column("Aliquot size", fn(Lot $lot) => $lot->getAliquoteSize()),
                new Column("Comment", fn(Lot $lot) => $lot->getComment()),
            ],
            isDisabled: fn(Lot $lot) => $lot->getAvailability() === Availability::Empty,
        );

        return $table;
    }
}