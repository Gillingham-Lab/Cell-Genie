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
use App\Entity\Toolbox\Toolbox;
use App\Entity\Toolbox\ViewTool;
use App\Repository\Experiment\ExperimentalRunRepository;
use App\Twig\Components\Date;
use App\Twig\Components\Trait\PaginatedRepositoryTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class ExperimentalRunTable extends AbstractController
{
    use DefaultActionTrait;
    use PaginatedRepositoryTrait;

    #[LiveProp]
    public ?ExperimentalDesign $design = null;

    public function __construct(
        ExperimentalRunRepository $repository
    ) {
        $this->setRepository($repository);
        $this->setPaginatedOrderBy(["createdAt" => "DESC"]);
    }

    public function getTable(): array
    {
        $paginatedRuns = $this->getPaginatedResults(searchFields: ["design" => $this->design->getId()->toRfc4122()]);

        $table = new Table(
            data: $paginatedRuns,
            columns: [
                new ToolboxColumn("", fn(ExperimentalRun $run) => new Toolbox([
                    new ViewTool(
                        # ToDo: Change this to lead to the run itself
                        path: $this->generateUrl("app_experiments"),
                        tooltip: "View run",
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
            maxRows: $this->getNumberOfRows(),
        );

        return $table->toArray();
    }
}