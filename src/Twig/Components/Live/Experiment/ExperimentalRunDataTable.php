<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalModel;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Entity\SubstanceLot;
use App\Entity\Table\Column;
use App\Entity\Table\ComponentColumn;
use App\Entity\Table\Table;
use App\Entity\Table\ToolboxColumn;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use App\Entity\Toolbox\ViewTool;
use App\Form\Experiment\ExperimentalSearchDataType;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Genie\Enums\FormRowTypeEnum;
use App\Service\Experiment\ExperimentalDataService;
use App\Twig\Components\Experiment\Datum;
use App\Twig\Components\Experiment\ModelView;
use App\Twig\Components\Trait\PaginatedRepositoryTrait;
use App\Twig\Components\Trait\PaginatedTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

/**
 * @phpstan-import-type ArrayTableShape from Table
 */
#[AsLiveComponent]
class ExperimentalRunDataTable extends AbstractController
{
    use DefaultActionTrait;
    use PaginatedTrait;
    /** @use PaginatedRepositoryTrait<never> */
    use PaginatedRepositoryTrait;

    #[LiveProp]
    public ?ExperimentalDesign $design;

    #[LiveProp]
    public ?ExperimentalRun $run = null;

    #[LiveProp]
    public string $liveSearchFormType = ExperimentalSearchDataType::class;

    /** @var array<string, mixed> */
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

    /**
     * @param ExperimentalDesign $design
     * @return Column[]
     */
    private function getTableColumns(ExperimentalDesign $design): array
    {
        $fields = $this->dataService->getFields($this->design)->toArray();

        $columns = [
            new ToolboxColumn("", function ($x) {
                return new Toolbox([
                    new ViewTool(
                        path: $this->generateUrl("app_experiments_run_view", ["run" => $x["run"]->getId()]),
                        tooltip: "View run",
                    ),
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

        $dataService = $this->dataService;
        $getColumnsFromFields = function ($fields, bool $small = false) use ($dataService): array {
            $columns = [];
            foreach ($fields as $field) {
                $formRow = $field->getFormRow();
                $columns[] = new ComponentColumn($field->getLabel(), function ($x) use ($field, $formRow, $small, $dataService) {
                    if (!array_key_exists($formRow->getFieldName(), $x)) {
                        return [
                            Datum::class, [
                                "field" => $field,
                                "formRow" => $formRow,
                                "datum" => null,
                                "small" => $small,
                            ]
                        ];
                    }

                    $value = $x[$formRow->getFieldName()];

                    if ($formRow->getType() === FormRowTypeEnum::FloatType) {
                        $configuration = $formRow->getConfiguration();
                        if (is_array($value)) {
                            $value = array_map(fn($x) => $dataService->convertFloatToString($x, $formRow), $value);
                        } else {
                            $value = $dataService->convertFloatToString($value, $formRow);
                        }
                    }

                    return [
                        Datum::class,
                        [
                            "field" => $field,
                            "formRow" => $formRow,
                            "datum" => $value,
                            "small" => $small,
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
                        columns: $getColumnsFromFields($dataFields, true),
                    ))->toArray(),
                    "small" => true,
                ],
            ]);
        }

        // Add models
        foreach ($design->getModels() as $model) {
            $columns[] = new ComponentColumn($model->getName(), function (array $x) use ($model) {
                /** @var ExperimentalRunCondition $condition */
                $condition = $x["set"];
                $conditionModel = $condition->getModels()->findFirst(fn (int $i, ExperimentalModel $conditionModel) => $conditionModel->getParent() === $model);
                return [
                    ModelView::class, [
                        "run" => $x["run"],
                        "condition" => $x["set"],
                        "model" => $conditionModel,
                        "showParams" => false,
                        "showWarnings" => false,
                        "showErrors" => false,
                        "width" => 400,
                        "oneTraceOnly" => true,
                    ]
                ];
            }, widthRecommendation: 40);
        }

        $columns[] = new Column("Path", fn ($x) => mb_str_shorten("{$x['run']->getName()}/{$x['set']->getName()}", 30));

        return $columns;
    }

    /**
     * @return ArrayTableShape
     * @throws \Exception
     */
    public function getTable(): array
    {
        $conditionFields = $this->dataService->getFields($this->design);

        $searchQuery = $this->searchQuery;

        if ($this->run) {
            $searchQuery["run"] = $this->run;
        }


        $dataRows = $this->dataService->getPaginatedResults(searchFields: $searchQuery, page: $this->page, limit: $this->limit, design: $this->design, limitRows: 10);

        $columns = $this->getTableColumns($this->design);

        $table = new Table(
            data: $dataRows,
            columns: $columns,
            maxRows: $this->dataService->getPaginatedResultCount(searchFields: $searchQuery, design: $this->design)
        );

        return $table->toArray();
    }

    /**
     * @param array<string, mixed> $search
     */
    #[LiveListener("search")]
    public function onSearch(
        #[LiveArg()]
        array $search = [],
    ): void {
        $this->searchQuery = [];

        foreach($search as $searchName => $searchValue) {
            if ($searchValue) {
                $this->searchQuery[$searchName] = $searchValue;
            }
        }

        $this->page = 0;
    }

    /**
     * @return array{fields: Collection<int, FormRow>, fieldChoices: array<string, string[]>}
     */
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
                // If entity type is not set, we skip the options
                $choices[$entityFormRow->getFieldName()] = [];
                continue;
            }

            // Entity types with subtypes (such as lots) are separated with a |.
            $entityType = explode("|", $entityType);
            $lotEntityChoices = [];
            $substanceEntityChoices = [];

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
                    foreach ($result->getLots() as $lot) {
                        $substanceLot = new SubstanceLot($result, $lot);
                        $lotEntityChoices[(string)$substanceLot] = $substanceLot->getLot()->getId()->toRfc4122();
                    }

                    $substanceEntityChoices[(string)$result] = method_exists($entityType, "getUlid") ? $result->getUlid()->toRfc4122() : $result->getId()->toRfc4122();
                }

                $choices[$entityFormRow->getFieldName() . "_lot"] = $lotEntityChoices;
                $choices[$entityFormRow->getFieldName() . "_substance"] = $substanceEntityChoices;
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
                    $lotEntityChoices[(string)$result] = method_exists($entityType, "getUlid") ? $result->getUlid()->toRfc4122() : $result->getId()->toRfc4122();
                }

                $choices[$entityFormRow->getFieldName()] = $lotEntityChoices;
            }
        }

        return [
            "fields" => $formRows,
            "fieldChoices" => $choices,
        ];
    }

    private function getDQLForFieldRole(ExperimentalFieldRole $role): string
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