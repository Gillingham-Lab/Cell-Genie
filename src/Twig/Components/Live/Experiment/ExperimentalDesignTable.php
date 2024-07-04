<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\Table\Column;
use App\Entity\Table\Table;
use App\Entity\Table\ToolboxColumn;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use App\Entity\Toolbox\ViewTool;
use App\Repository\Experiment\ExperimentalDesignRepository;
use App\Twig\Components\Trait\PaginatedTrait;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use UnhandledMatchError;

#[AsLiveComponent]
class ExperimentalDesignTable extends AbstractController
{
    use DefaultActionTrait;
    use PaginatedTrait;

    public function __construct(
       private ExperimentalDesignRepository $designRepository,
    ) {

    }

    public function getNumberOfRows(): ?int
    {
        if ($this->numberOfRows === null) {
            $numberOfRows = $this->getPaginatedResults()->count();
            $this->setNumberOfRows($numberOfRows);
        }

        return $this->numberOfRows;
    }

    /**
     * Returns the array-converted table of found entities
     * @throws Exception
     */
    public function getTable(): array
    {
        $paginatedDesigns = $this->getPaginatedResults(false);

        $table = new Table(
            data: $paginatedDesigns,
            columns: [
                new ToolboxColumn("", fn(ExperimentalDesign $design) => new Toolbox([
                    new ViewTool(
                        path: $this->generateUrl("app_experiments"),
                        tooltip: "View experiment",
                    ),
                    new EditTool(
                        path: $this->generateUrl("app_experiments_edit", ["design" => $design->getId()]),
                        tooltip: "Edit experiment",
                    ),
                ])),
                new Column("Nr", fn(ExperimentalDesign $design) => $design->getNumber(), bold: true),
                new Column("Name", fn(ExperimentalDesign $design) => $design->getShortName()),
            ],
            maxRows: $this->getNumberOfRows(),
        );

        return $table->toArray();
    }

    private function getPaginatedResults(bool $omitAliquots = true): Paginator
    {
        try {
            $paginatedDesigns = $this->designRepository->getPaginatedExperiments(
                orderBy: ["number" => "ASC"],
                page: $this->page,
                limit: $this->limit,
            );
        } catch (UnhandledMatchError $e) {
            throw new Exception("An error occured during the query.");
        }

        return $paginatedDesigns;
    }
}