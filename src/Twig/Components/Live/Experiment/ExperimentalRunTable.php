<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\Table\Column;
use App\Entity\Table\ComponentColumn;
use App\Entity\Table\Table;
use App\Entity\Table\ToolboxColumn;
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
    public ?ExperimentalRun $run = null;

    public function __construct(
        ExperimentalRunRepository $repository
    ) {
        $this->setRepository($repository);
        $this->setPaginatedOrderBy(["createdAt" => "DESC"]);
    }

    public function getTable(): array
    {
        $paginatedRuns = $this->getPaginatedResults();

        $table = new Table(
            data: $paginatedRuns,
            columns: [
                new ToolboxColumn("", fn(ExperimentalRun $run) => new Toolbox([
                    new ViewTool(
                        # ToDo: Change this to lead to the run itself
                        path: $this->generateUrl("app_experiments"),
                    ),
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