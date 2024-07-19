<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDatum;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Entity\ExperimentalCondition;
use App\Entity\SubstanceLot;
use App\Entity\Table\Column;
use App\Entity\Table\ComponentColumn;
use App\Entity\Table\Table;
use App\Entity\Table\ToolboxColumn;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use App\Form\Experiment\ExperimentalSearchDataType;
use App\Genie\Enums\DatumEnum;
use App\Genie\Enums\FormRowTypeEnum;
use App\Service\Experiment\ExperimentalDataService;
use App\Twig\Components\Experiment\Datum;
use App\Twig\Components\Trait\PaginatedRepositoryTrait;
use App\Twig\Components\Trait\PaginatedTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
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

    #[LiveProp]
    public string $liveSearchFormType = ExperimentalSearchDataType::class;

    #[LiveProp(url: true)]
    public array $searchResults = [];

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
        $columns = [
            new ToolboxColumn("", function ($x) {
                return new Toolbox([
                    new EditTool(
                        path: $this->generateUrl("app_experiments_run_edit", ["run" => $x["run"]->getId()]),
                        tooltip: "Edit run"
                    ),
                    new EditTool(
                        path: $this->generateUrl("app_experiments_run_addData", ["run" => $x["run"]->getId()]),
                        tooltip: "Edit data",
                        icon: "data",
                        iconStack: "edit",
                    ),
                ]);
            })
        ];

        foreach ($fields as $field) {
            $formRow = $field->getFormRow();

            $columns[] = new ComponentColumn($field->getLabel(), function ($x) use ($field, $formRow) {
                $value = $x[$formRow->getFieldName()];

                if ($formRow->getType() === FormRowTypeEnum::FloatType) {
                    if (is_infinite($value) or is_nan($value)) {
                        $configuration = $formRow->getConfiguration();
                        if ($configuration["floattype_inactive_label"] ?? null) {
                            $valueInstead = $configuration["floattype_inactive_label"];

                            if (
                                ($configuration["floattype_inactive"] === "Inf" and is_infinite($value) and $value > 0) or
                                ($configuration["floattype_inactive"] === "-Inf" and is_infinite($value) and $value > 0) or
                                ($configuration["floattype_inactive"] === "NaN" and is_nan($value))
                            ) {
                                $value = $valueInstead;
                            }
                        }
                    }
                }

                return [
                    Datum::class,
                    [
                        "field" => $field,
                        "formRow" => $formRow,
                        "datum" => $value,
                    ]
                ];
            });
        }


        $columns[] = new Column("Path", fn ($x) => "{$x['run']->getName()}/{$x['set']->getName()}");

        return $columns;
    }

    public function getTable(): array
    {
        $conditionFields = $this->dataService->getFields($this->design);
        $conditions = $this->dataService->getPaginatedResults(searchFields: $this->searchResults, design: $this->design, page: $this->page, limit: $this->limit);

        $columns = $this->getTableColumns(... $conditionFields);

        $table = new Table(
            data: $conditions,
            columns: $columns,
            maxRows: $this->dataService->getPaginatedResultCount(searchFields: $this->searchResults, design: $this->design)
        );

        return $table->toArray();
    }

    #[LiveListener("search")]
    public function onSearch(
        #[LiveArg()]
        array $search = [],
    ) {
        $this->searchResults = [];

        foreach($search as $searchName => $searchValue) {
            if ($searchValue) {
                $this->searchResults[$searchName] = $searchValue;
            }
        }

        $this->page = 0;
    }

    public function getSearchFormOptions(): array
    {
        $formRows = $this->dataService->getFields($this->design)->map(fn (ExperimentalDesignField $field) => $field->getFormRow());
        $choices = [];

        #$entityFields = $this->dataService->getFields($this->design)->filter(fn (ExperimentalDesignField $field) => $field->getFormRow()->getType() === FormRowTypeEnum::EntityType);
        $entityFormRows = $formRows->filter(fn (FormRow $row) => $row->getType() === FormRowTypeEnum::EntityType);
        /** @var FormRow $entityFormRow */
        foreach ($entityFormRows as $entityFormRow) {
            $entityType = $entityFormRow->getConfiguration()["entityType"] ?? null;
            if ($entityType === null) {
                $choices[$entityFormRow->getFieldName()] = [];
            }

            // Entity types with subtypes (such as lots) are separated with a |.
            $entityType = explode("|", $entityType);
            $entityChoices = [];

            $queryBuilder = $this->entityManager->createQueryBuilder();
            $expression = $this->entityManager->getExpressionBuilder();

            $subQuery = $this->entityManager->createQueryBuilder()
                ->from(ExperimentalDesign::class, "ed")
                ->leftJoin("ed.runs", "edr")
                ->leftJoin("edr.conditions", "edrc")
                ->leftJoin("edrc.data", "edrcd")
                ->select("edrcd.referenceUuid")
                ->where("edrcd.name = :name")
                ->getDQL()
            ;

            if (count($entityType) > 1) {
                $entityType = $entityType[1];

                $queryBuilder = $queryBuilder
                    ->from($entityType, "s")
                    ->select("s")
                    ->leftJoin("s.lots", "l")
                    ->addSelect("l")
                    ->where($expression->in("l.id", $subQuery))
                    ->setParameter("name", $entityFormRow->getFieldName());

                $results = $queryBuilder->getQuery()->getResult();

                foreach ($results as $result) {
                    $substanceLot = new SubstanceLot($result, $result->getLots()->first());
                    $entityChoices[(string)$substanceLot] = $substanceLot->getLot()->getId()->toRfc4122();
                }
            } else {
                $entityType = $entityType[0];

                $queryBuilder = $queryBuilder
                    ->from($entityType, "e")
                    ->select("e")
                    ->where($expression->in(
                        method_exists($entityType, "getUlid") ? "e.ulid" : "e.id",
                        $subQuery
                    ))
                    ->setParameter("name", $entityFormRow->getFieldName());
                ;

                $results = $queryBuilder->getQuery()->getResult();

                foreach ($results as $result) {
                    $entityChoices[(string)$result] = method_exists($entityType, "getUlid") ? $result->getUlid()->toRfc4122() : $result->getId()->toRfc4122();
                }
            }

            $choices[$entityFormRow->getFieldName()] = $entityChoices;
        }

        return [
            "fields" => $formRows,
            "fieldChoices" => $choices,
        ];
    }
}