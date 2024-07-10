<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\ExperimentalCondition;
use App\Entity\Table\Column;
use App\Entity\Table\ComponentColumn;
use App\Entity\Table\Table;
use App\Genie\Enums\DatumEnum;
use App\Service\Experiment\ExperimentalDataService;
use App\Twig\Components\Experiment\Datum;
use App\Twig\Components\Trait\PaginatedRepositoryTrait;
use App\Twig\Components\Trait\PaginatedTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class ExperimentalRunDataTable extends AbstractController
{
    use DefaultActionTrait;
    use PaginatedTrait;
    use PaginatedRepositoryTrait;

    #[LiveProp]
    public ?ExperimentalDesign $design;

    public function __construct(
        private readonly ExperimentalDataService $dataService,
        private readonly EntityManagerInterface $entityManager,
    ) {

    }

    public function getNumberOfRows(): ?int
    {
        if ($this->numberOfRows === null) {
            $this->numberOfRows = $this->dataService->getPaginatedResultCount(design: $this->design);
        }

        return $this->numberOfRows;
    }

    private function getTableColumns(ExperimentalDesignField ... $fields): array
    {
        $columns = [];

        foreach ($fields as $field) {
            $formRow = $field->getFormRow();

            $columns[] = new ComponentColumn($field->getLabel(), fn ($x) => [
                Datum::class,
                [
                    "field" => $field,
                    "formRow" => $formRow,
                    "datum" => $x[$formRow->getFieldName()],
                ]
            ]);
        }


        $columns[] = new Column("Run", fn ($x) => $x["run"]->getName());
        $columns[] = new Column("Condition", fn ($x) => $x["set"]->getName());

        return $columns;
    }

    public function getTable(): array
    {
        $conditionFields = $this->dataService->getFields($this->design);
        $conditions = $this->dataService->getPaginatedResults(design: $this->design, page: $this->page, limit: $this->limit);

        $columns = $this->getTableColumns(... $conditionFields);

        $table = new Table(
            data: $conditions,
            columns: $columns,
            maxRows: $this->dataService->getPaginatedResultCount(design: $this->design)
        );

        return $table->toArray();
    }
}