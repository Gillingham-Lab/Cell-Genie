<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDatum;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Entity\ExperimentalCondition;
use App\Entity\Table\Column;
use App\Entity\Table\ComponentColumn;
use App\Entity\Table\Table;
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

            if (is_array($entityType)) {
                $result = [];
            } else {
                $queryBuilder = $this->entityManager->createQueryBuilder();
                $expression = $this->entityManager->getExpressionBuilder();
                $queryBuilder = $queryBuilder
                    ->from($entityType, "e")
                    ->select("e")
                    ->where($expression->in(
                        method_exists($entityType, "getUlid") ? "e.ulid" : "e.id",
                        $this->entityManager->createQueryBuilder()
                            ->from(ExperimentalDesign::class, "ed")
                            ->leftJoin("ed.runs", "edr")
                            ->leftJoin("edr.conditions", "edrc")
                            ->leftJoin("edrc.data", "edrcd")
                            ->select("edrcd.referenceUuid")
                            ->where("edrcd.name = :name")
                            ->getDQL()
                    ))
                    ->setParameter("name", $entityFormRow->getFieldName());
                ;

                $results = $queryBuilder->getQuery()->getResult();
            }

            $entityChoices = [];
            foreach ($results as $result) {
                $entityChoices[(string)$result] = method_exists($entityType, "getUlid") ? $result->getUlid()->toRfc4122() : $result->getId()->toRfc4122();
            }

            $choices[$entityFormRow->getFieldName()] = $entityChoices;
        }

        return [
            "fields" => $formRows,
            "fieldChoices" => $choices,
        ];
    }
}