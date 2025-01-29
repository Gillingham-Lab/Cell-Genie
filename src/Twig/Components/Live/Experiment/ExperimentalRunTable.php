<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\Table\Column;
use App\Entity\Table\ComponentColumn;
use App\Entity\Table\Table;
use App\Entity\Table\ToolboxColumn;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Tool;
use App\Entity\Toolbox\Toolbox;
use App\Entity\Toolbox\ViewTool;
use App\Repository\Experiment\ExperimentalRunRepository;
use App\Twig\Components\Date;
use App\Twig\Components\Trait\PaginatedRepositoryTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * @phpstan-import-type ArrayTableShape from Table
 */
#[AsLiveComponent]
class ExperimentalRunTable extends AbstractController
{
    use DefaultActionTrait;
    /** @use PaginatedRepositoryTrait<ExperimentalRun> */
    use PaginatedRepositoryTrait;

    #[LiveProp]
    public ?ExperimentalDesign $design = null;

    public function __construct(
        ExperimentalRunRepository $repository
    ) {
        $this->setRepository($repository);
        $this->setPaginatedOrderBy(["createdAt" => "DESC"]);
    }

    /**
     * @return ArrayTableShape
     * @throws \Exception
     */
    public function getTable(): array
    {
        $paginatedRuns = $this->getPaginatedResults(searchFields: ["design" => $this->design->getId()->toRfc4122()]);

        $table = new Table(
            data: $paginatedRuns,
            columns: [
                new ToolboxColumn("", fn(ExperimentalRun $run) => new Toolbox([
                    new ViewTool(
                        # ToDo: Change this to lead to the run itself
                        path: $this->generateUrl("app_experiments_run_view", ["run" => $run->getId()]),
                        tooltip: "View run",
                    ),
                    new Tool(
                        $this->generateUrl("app_api_experiments_run_view_data", ["run" => $run->getId()]),
                        icon: "download",
                        buttonClass: "btn-secondary",
                        tooltip: "Download data as tsv"
                    ),
                    new Tool(
                        $this->generateUrl("app_experiments_run_clone", ["run" => $run->getId()]),
                        icon: "clone",
                        buttonClass: "btn-warning",
                        tooltip: "Clone run",
                    ),
                    new EditTool(
                        path: $this->generateUrl("app_experiments_run_edit", ["run" => $run->getId()]),
                        tooltip: "Edit run",
                    ),
                    new EditTool(
                        path: $this->generateUrl("app_experiments_run_addData", ["run" => $run->getId()]),
                        icon: "data",
                        tooltip: "Edit run data",
                        iconStack: "edit",
                    )
                ])),
                new Column("Name", fn(ExperimentalRun $run) => $run->getName()),
                new Column("Scientist", fn(ExperimentalRun $run) => $run->getScientist()),
                new ComponentColumn("Created", fn(ExperimentalRun $run) => [
                    Date::class,
                    [
                        "dateTime" => $run->getCreatedAt(),
                    ]
                ]),
                new ComponentColumn("Modified", fn(ExperimentalRun $run) => [
                    Date::class,
                    [
                        "dateTime" => $run->getModifiedAt(),
                    ]
                ])
            ],
            maxRows: $this->getNumberOfRows(searchFields: ["design" => $this->design->getId()->toRfc4122()]),
        );

        return $table->toArray();
    }
}