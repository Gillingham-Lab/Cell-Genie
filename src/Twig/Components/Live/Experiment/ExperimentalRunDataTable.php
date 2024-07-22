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
use App\Genie\Enums\ExperimentalFieldRole;
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
    public array $searchQuery = [];

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
                        icon: "data",
                        tooltip: "Edit data",
                        iconStack: "edit",
                    ),
                ]);
            })
        ];

        $specialFloatToString = function($value, array $configuration): string|float {
            if (is_float($value) === false) {
                return "NAN";
            }

            if (is_infinite($value) or is_nan($value)) {
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

            return $value;
        };

        $getColumnsFromFields = function ($fields) use ($specialFloatToString): array {
            $columns = [];
            foreach ($fields as $field) {
                $formRow = $field->getFormRow();
                $columns[] = new ComponentColumn($field->getLabel(), function ($x) use ($field, $formRow, $specialFloatToString) {
                    if (!array_key_exists($formRow->getFieldName(), $x)) {
                        return [
                            Datum::class, [
                                "field" => $field,
                                "formRow" => $formRow,
                                "datum" => null,
                            ]
                        ];
                    }

                    $value = $x[$formRow->getFieldName()];

                    if ($formRow->getType() === FormRowTypeEnum::FloatType) {
                        $configuration = $formRow->getConfiguration();
                        if (is_array($value)) {
                            $value = array_map(fn($x) => $specialFloatToString($x, $configuration), $value);
                        } else {
                            $value = $specialFloatToString($value, $configuration);
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

            return $columns;
        };

        $fieldColumns = $getColumnsFromFields(array_filter($fields, fn (ExperimentalDesignField $field) => in_array($field->getRole(), [ExperimentalFieldRole::Top, ExperimentalFieldRole::Condition])));
        $dataFields = array_filter($fields, fn (ExperimentalDesignField $field) => !in_array($field->getRole(), [ExperimentalFieldRole::Top, ExperimentalFieldRole::Condition]));

        $columns = [
            ... $columns,
            ... $fieldColumns,
        ];

        if (count($dataFields) > 0) {
            $columns[] = new ComponentColumn("Data", fn($x) => [
                \App\Twig\Components\Table::class, [
                    "table" => (new Table(
                        data: $x["data"] ?? [],
                        columns: $getColumnsFromFields($dataFields),
                    ))->toArray(),
                    "small" => true,
                ],
            ]);
        }

        $columns[] = new Column("Path", fn ($x) => "{$x['run']->getName()}/{$x['set']->getName()}");

        return $columns;
    }

    public function getTable(): array
    {
        $conditionFields = $this->dataService->getFields($this->design);
        $dataRows = $this->dataService->getPaginatedResults(searchFields: $this->searchQuery, design: $this->design, page: $this->page, limit: $this->limit);

        $columns = $this->getTableColumns(... $conditionFields);

        $table = new Table(
            data: $dataRows,
            columns: $columns,
            maxRows: $this->dataService->getPaginatedResultCount(searchFields: $this->searchQuery, design: $this->design)
        );

        return $table->toArray();
    }

    #[LiveListener("search")]
    public function onSearch(
        #[LiveArg()]
        array $search = [],
    ) {
        $this->searchQuery = [];

        foreach($search as $searchName => $searchValue) {
            if ($searchValue) {
                $this->searchQuery[$searchName] = $searchValue;
            }
        }

        $this->page = 0;
    }

    public function getSearchFormOptions(): array
    {
        $fields = $this->dataService->getFields($this->design);
        $formRows = $fields->map(fn (ExperimentalDesignField $field) => $field->getFormRow());

        $choices = [];

        $entityFields = $fields->filter(fn (ExperimentalDesignField $row) => $row->getFormRow()->getType() === FormRowTypeEnum::EntityType);

        /** @var ExperimentalDesignField $entityField */
        foreach ($entityFields as $entityField) {
            $entityFormRow = $entityField->getFormRow();

            $entityType = $entityFormRow->getConfiguration()["entityType"] ?? null;
            if ($entityType === null) {
                $choices[$entityFormRow->getFieldName()] = [];
            }

            // Entity types with subtypes (such as lots) are separated with a |.
            $entityType = explode("|", $entityType);
            $entityChoices = [];

            $queryBuilder = $this->entityManager->createQueryBuilder();
            $expression = $this->entityManager->getExpressionBuilder();

            $subQuery = $this->getDQLForFieldRole($entityField->getRole());

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

    private function getDQLForFieldRole(ExperimentalFieldRole $role): ?string
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from(ExperimentalDesign::class, "ed")
            ->leftJoin("ed.runs", "edr")
        ;

        $queryBuilder = match($role) {
            ExperimentalFieldRole::Top => $queryBuilder
                ->leftJoin("edr.data", "edrd")
                ->select("edrd.referenceUuid")
                ->where("edrd.name = :name"),

            ExperimentalFieldRole::Condition => $queryBuilder
                ->leftJoin("edr.conditions", "edrc")
                ->leftJoin("edrc.data", "edrcd")
                ->select("edrcd.referenceUuid")
                ->where("edrcd.name = :name"),

            ExperimentalFieldRole::Comparison, ExperimentalFieldRole::Datum => $queryBuilder
                ->leftJoin("edr.dataSets", "edrds")
                ->leftJoin("edrds.data", "edrdsd")
                ->select("edrdsd.referenceUuid")
                ->where("edrdsd.name = :name"),
        };

        return $queryBuilder->getDQL();
    }
}