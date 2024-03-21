<?php
declare(strict_types=1);

namespace App\Twig\Components\Live;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\Table\Column;
use App\Entity\Table\Table;
use App\Entity\Table\ToggleColumn;
use App\Entity\Table\ToolboxColumn;
use App\Entity\Table\UrlColumn;
use App\Entity\Toolbox\AddTool;
use App\Entity\Toolbox\ClipwareTool;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use App\Entity\Toolbox\ViewTool;
use App\Form\Search\CellSearchType;
use App\Repository\Cell\CellRepository;
use App\Service\Doctrine\Type\Ulid;
use App\Twig\Components\Trait\PaginatedTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactoryInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Twig\Error\RuntimeError;
use UnhandledMatchError;

#[AsLiveComponent]
class CellTable extends AbstractController
{
    use DefaultActionTrait;
    use PaginatedTrait;

    public string $entityClass;
    public string $entityContext;

    #[LiveProp]
    public string $liveSearchFormType = CellSearchType::class;

    #[LiveProp(url: true)]
    public array $searchResults = [];

    public function __construct(
        private CellRepository $cellRepository,
    ) {
    }

    #[LiveListener("search")]
    public function onSearch(
        #[LiveArg] ?string $cellNumber = null,
        #[LiveArg] ?string $cellIdentifier = null,
        #[LiveArg] ?string $cellName = null,
        #[LiveArg] ?string $cellGroupName = null,
        #[LiveArg] ?string $groupOwner = null,
        #[LiveArg] ?string $isCancer = null,
        #[LiveArg] ?string $isEngineered = null,
        #[LiveArg] ?int $organism = null,
        #[LiveArg] ?int $tissue = null,
    ) {
        $this->searchResults = [
            "cellNumber" => $cellNumber,
            "cellIdentifier" => $cellIdentifier,
            "cellName" => $cellName,
            "cellGroupName" => $cellGroupName,
            "groupOwner" => $groupOwner === null ? null : Ulid::fromString($groupOwner)->toRfc4122(),
            "isCancer" => $isCancer === null ? null : $isCancer === "true",
            "isEngineered" => $isEngineered === null ? null : $isEngineered === "true",
            "organism" => $organism,
            "tissue" => $tissue,
        ];

        // Set page to 0
        $this->page = 0;
    }

    private function getPaginatedResults(bool $omitAliquots = true): Paginator
    {
        try {
            $paginatedCells = $this->cellRepository->getPaginatedCellsWithAliquots(
                orderBy: ["cellNumber" => "ASC"],
                searchFields: $this->searchResults,
                page: $this->page,
                limit: $this->limit,
                omitAliquots: $omitAliquots,
            );
        } catch (UnhandledMatchError $e) {
            throw new Exception("An error occured during the query.");
        }

        return $paginatedCells;
    }

    /**
     * Returns the array-converted table of found entities
     * @return array
     * @throws Exception
     */
    public function getTable()
    {
        $paginatedCells = $this->getPaginatedResults(false);

        $table = new Table(
            data: $paginatedCells,
            columns: [
                new ToolboxColumn("", fn(Cell $cell) => new Toolbox([
                    new ViewTool(
                        path: $cell->getCellNumber() ? $this->generateUrl("app_cell_view_number", ["cellNumber" => $cell->getCellNumber()]) : $this->generateUrl("app_cell_view", ["cellId" => $cell->getId()]),
                        tooltip: "View Cell",
                    ),
                    new ClipwareTool(
                        clipboardText: $cell->getName() . ($cell->getRrid() ? " (RRID:{$cell->getRrid()})" : ""),
                        tooltip: "Copy citation on cell",
                    ),
                    new EditTool(
                        path: $this->generateUrl("app_cell_edit", ["cell" => $cell->getCellNumber()]),
                        tooltip: "Edit cell",
                    ),
                    new AddTool(
                        path: $this->generateUrl("app_cell_aliquot_add", ["cell" => $cell->getCellNumber()])
                    )
                ])),
                new Column("Nr", fn(Cell $cell) => $cell->getCellNumber()),
                new Column("Name", fn(Cell $cell) => $cell->getName()),
                new Column("Cell group", fn(Cell $cell) => $cell->getCellGroup()->getName()),
                new UrlColumn("RRID", function(Cell $cell) {
                    return $cell->getRrid() ? [
                        "href" => "",
                        "label" => $cell->getRrid(),
                    ] : null;
                }),
                new Column("Aliquots", function(Cell $cell) {
                    $unique = $cell->getCellAliquotes()->count();
                    $total = array_sum($cell->getCellAliquotes()->map(fn(CellAliquot $aliquot) => $aliquot->getVials())->toArray());

                    return "Total: {$total}, Unique: {$unique}";
                }),
                new Column("Group", fn(Cell $cell) => $cell->getGroup()->getShortName()),
                new Column("Organism", fn(Cell $cell) => $cell->getOrganism()?->getName() ?? "Unknown"),
                new Column("Tissue", fn(Cell $cell) => $cell->getTissue()?->getName() ?? "Unknown"),
                new ToggleColumn("Cancer", fn(Cell $cell) => $cell->getIsCancer()),
                new ToggleColumn("Engineered", fn(Cell $cell) => $cell->getIsEngineered()),
            ],
            maxRows: $this->getPaginatedResults(true)->count(),
        );

        return $table->toArray();
    }
}