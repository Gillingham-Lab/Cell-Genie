<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\Table\Column;
use App\Entity\Table\Table;
use App\Entity\Table\ToolboxColumn;
use App\Entity\Toolbox\AddTool;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use App\Entity\Toolbox\ViewTool;
use App\Repository\Experiment\ExperimentalDesignRepository;
use App\Twig\Components\Trait\PaginatedRepositoryTrait;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * @phpstan-import-type ArrayTableShape from Table
 */
#[AsLiveComponent]
class ExperimentalDesignTable extends AbstractController
{
    use DefaultActionTrait;
    /** @use PaginatedRepositoryTrait<ExperimentalDesign> */
    use PaginatedRepositoryTrait;

    public function __construct(
        ExperimentalDesignRepository $repository,
    ) {
        $this->setRepository($repository);
        $this->setPaginatedOrderBy(["number" => "ASC"]);
    }

    /**
     * Returns the array-converted table of found entities
     *
     * @return ArrayTableShape
     * @throws Exception
     */
    public function getTable(): array
    {
        $paginatedDesigns = $this->getPaginatedResults();

        $table = new Table(
            data: $paginatedDesigns,
            columns: [
                new ToolboxColumn("", fn(ExperimentalDesign $design) => new Toolbox([
                    new ViewTool(
                        path: $this->generateUrl("app_experiments_view", ["design" => $design->getId()]),
                        tooltip: "View experiment",
                    ),
                    new ViewTool(
                        path: $this->generateUrl("app_experiments_view_data", ["design" => $design->getId()]),
                        icon: "data",
                        tooltip: "View data",
                        iconStack: "view",
                    ),
                    new ViewTool(
                        path: $this->generateUrl("app_api_experiments_view_data", ["design" => $design->getId()]),
                        icon: "data",
                        buttonClass: "btn-secondary",
                        tooltip: "Download data",
                        iconStack: "download",
                    ),
                    new EditTool(
                        path: $this->generateUrl("app_experiments_edit", ["design" => $design->getId()]),
                        tooltip: "Edit experiment",
                    ),
                    new AddTool(
                        path: $this->generateUrl("app_experiments_run_new", ["design" => $design->getId()]),
                        tooltip: "Add experiment",
                    ),
                ])),
                new Column("Nr", fn(ExperimentalDesign $design) => $design->getNumber(), bold: true),
                new Column("Name", fn(ExperimentalDesign $design) => $design->getShortName()),
            ],
            maxRows: $this->getNumberOfRows(),
        );

        return $table->toArray();
    }
}
