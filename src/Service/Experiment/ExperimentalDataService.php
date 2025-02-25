<?php
declare(strict_types=1);

namespace App\Service\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDatum;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalModel;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunDataSet;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Entity\DoctrineEntity\Lot;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Form\ScientificNumberTransformer;
use App\Form\Search\NumberSearchTransformer;
use App\Genie\Codec\ExperimentValueCodec;
use App\Genie\Enums\DatumEnum;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Genie\Enums\FloatTypeEnum;
use App\Genie\Enums\FormRowTypeEnum;
use App\Genie\Enums\IntegerTypeEnum;
use App\Repository\LotRepository;
use App\Service\Doctrine\SearchService;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Psr\Log\LoggerInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Uid\Uuid;

readonly class ExperimentalDataService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SearchService $searchService,
        private ExperimentalModelService $modelService,
        private Stopwatch $stopwatch,
        private LoggerInterface $logger,
    ) {

    }

    private function getBaseQuery(ExperimentalDesign $design): QueryBuilder
    {
        $dataFieldsToFetch = [];
        foreach ($design->getFields() as $field) {
            if ($field->isExposed()) {
                $dataFieldsToFetch[] = $field->getFormRow()->getFieldName();
            }
        }

        $qb = $this->entityManager->createQueryBuilder();

        return $qb
            ->select("exrc")
            ->addSelect("exrcmodel")
            ->from(ExperimentalRunCondition::class, "exrc")

            ->leftJoin("exrc.experimentalRun", "exr")
            ->leftJoin("exrc.models", "exrcmodel")
            ->addSelect("exr")
            ->where("exr.design = :design")

            ->setParameter("design", $design->getId()->toRfc4122())
            ;
    }

    /**
     * @return Collection<int, ExperimentalDesignField>
     */
    public function getFields(ExperimentalDesign $design, bool $onlyExposed = true): Collection
    {
        if ($onlyExposed) {
            if (method_exists($design->getFields(), "isInitialized") && $design->getFields()->isInitialized() === false) {
                $fields = $design->getFields()->matching((new Criteria())->where(new Comparison("exposed", "=", "true")));
            } else {
                $fields = $design->getFields()->filter(fn (ExperimentalDesignField $field) => $field->isExposed());
            }
        } else {
            $fields = $design->getFields();
        }

        return $fields;
    }

    /**
     * @param null|array<string, "ASC"|"DESC"> $orderBy
     * @param array<string, mixed> $searchFields
     */
    private function getResults(
        ?array $orderBy = null,
        array $searchFields = [],
        ?ExperimentalDesign $design = null
    ): QueryBuilder {
        $queryBuilder = $this->getBaseQuery($design);

        if (!empty($searchFields)) {
            $queryBuilder = $this->addSearchFields($queryBuilder, $searchFields, $design);
        }

        if (empty($orderBy)) {
            $queryBuilder->addOrderBy("exr.createdAt", "DESC");
        }

        return $queryBuilder;
    }

    /**
     * @param null|array<string, "ASC"|"DESC"> $orderBy
     * @param array<string, mixed> $searchFields
     * @return mixed[]
     */
    public function getPaginatedResults(
        ?array $orderBy = null,
        array $searchFields = [],
        int $page = 0,
        int $limit = 30,
        ?ExperimentalDesign $design = null,
        ?int $limitRows = null,
    ): array {
        if ($design === null) {
            throw new \Exception("You must give an experimental design.");
        }

        // Retrieve (paginated) conditions to show in the table.
        $queryBuilder = $this->getResults($orderBy, $searchFields, $design);
        $queryBuilder = $queryBuilder
            ->setFirstResult($limit * $page)
            ->setMaxResults($limit)
        ;

        $this->logger->debug("ExperimentalDataService.getPaginatedResults: Create Paginator");

        // Retrieve rows
        /** @var Paginator<ExperimentalRunCondition> $paginatedConditions */
        $paginatedConditions = new Paginator($queryBuilder->getQuery(), fetchJoinCollection: true);

        $conditionIds = array_unique(array_map(fn (ExperimentalRunCondition $condition) => $condition->getId()->toRfc4122(), $paginatedConditions->getIterator()->getArrayCopy()));

        $this->logger->debug("ExperimentalDataService.getPaginatedResults: Retrieving collected conditions");
        $this->stopwatch->start("experimentalDataService.getPaginatedResults.HydratedConditionDatum");

        // Prefetch run and condition data
        $hydratedConditionDatum = $this->entityManager->createQueryBuilder()
            ->from(ExperimentalRunCondition::class, "condition", indexBy: "condition.id")
            ->select("condition")
            ->addSelect("data")
            ->addSelect("run")
            ->addSelect("runData")
            ->addSelect("models")
            ->leftJoin("condition.experimentalRun", "run")
            ->leftJoin("condition.models", "models")
            ->leftJoin("run.data", "runData", indexBy: "runData.name")
            ->leftJoin("condition.data", "data", indexBy: "data.name")
            ->where("condition.id IN (:conditions)")
            ->setParameter("conditions", $conditionIds)
            ->getQuery()
            ->getResult();

        $this->stopwatch->stop("experimentalDataService.getPaginatedResults.HydratedConditionDatum");

        // Prefetch run datasets
        $runIds = array_map(fn (ExperimentalRunCondition $condition) => $condition->getExperimentalRun()->getId()->toRfc4122(), $hydratedConditionDatum);
        $runIds = array_unique($runIds);

        $hydratedDataSets = $this->entityManager->createQueryBuilder()
            ->from(ExperimentalRun::class, "run", indexBy: "run.id")
            ->select("run")
            ->addSelect("dataSet")
            ->addSelect("data")
            ->leftJoin("run.dataSets", "dataSet")
            ->leftJoin("dataSet.data", "data")
            ->where("run.id IN (:runIds)")
            ->setParameter("runIds", $runIds)
            ->getQuery()
            ->getResult();

        // Prefetch entities
        $entitiesToFetch = $this->getListOfEntitiesToFetch($paginatedConditions, $design);
        $entities = $this->fetchEntitiesFromList($entitiesToFetch);

        // Create the data array
        return $this->createDataArray($paginatedConditions, $entities, $design, maxRows: $limitRows);
    }

    /**
     * Gets a list of entity IDs from the experimental design to fetch from the database
     *
     * @param Collection<int, ExperimentalRunCondition> $conditions
     * @param ExperimentalDesign $design
     * @return array<class-string<object>, array{str: true|string|object}>
     */
    public function getListOfEntitiesToFetch(iterable $conditions, ExperimentalDesign $design): array
    {
        $entitiesToFetch = [];
        $datumConfiguration = [];

        $pushEntity = function(Collection $data, &$entitiesToFetch) use ($design) {
            foreach ($data as $datum) {
                if ($datum->getType() === DatumEnum::EntityReference) {
                    /**
                     * @var Uuid $id
                     * @var string $class FQCN
                     */
                    [$id, $class] = $datum->getValue();

                    // Initialize list for that specific class if not yet done
                    if (!isset($entitiesToFetch[$class])) {
                        $entitiesToFetch[$class] = [];
                    }

                    // Check if the datum configuration has two classes
                    /** @var ExperimentalDesignField $field */
                    $field = $design->getFields()->filter(fn (ExperimentalDesignField $x) => $x->getFormRow()->getFieldName() === $datum->getName())->first();

                    // Nothing to retrieve if the field is not exposed
                    if (!($field instanceof ExperimentalDesignField) or !$field->isExposed()) {
                        return;
                    }

                    $configuredEntityTypes = explode("|", $field->getFormRow()->getConfiguration()["entityType"] ?? "");

                    // Single class
                    if (count($configuredEntityTypes) <= 1) {
                        $entitiesToFetch[$class][$id->toRfc4122()] = true;
                    } else {
                        $entitiesToFetch[$class][$id->toRfc4122()] = $configuredEntityTypes[1];
                    }
                }
            }
        };

        /** @var ExperimentalRunCondition $condition */
        foreach ($conditions as $condition) {
            // Top values
            $pushEntity($condition->getExperimentalRun()->getData(), $entitiesToFetch);

            // Condition values
            $pushEntity($condition->getData(), $entitiesToFetch);

            // Datasets
            foreach ($condition->getExperimentalRun()->getDataSets()->filter(fn (ExperimentalRunDataSet $set) => $set->getCondition() === $condition) as $dataSet) {
                $pushEntity($dataSet->getData(), $entitiesToFetch);
            }
        }

        return $entitiesToFetch;
    }

    /**
     * Fetches a list of entities by trying to get their repository and writes the fetched entities back into the original array.
     *
     * @param array<class-string<object>, array{str: true|string|object}> $entitiesToFetch
     * @return array<class-string<object>, array{str: object}>
     */
    public function fetchEntitiesFromList(array $entitiesToFetch): array
    {
        foreach ($entitiesToFetch as $class => $entities) {
            if (current($entities) === true) {
                $entityRepository = $this->entityManager->getRepository($class);

                if (method_exists($class, "getUlid")) {
                    $idField = "ulid";
                    $idMethod = "getUlid";
                } else {
                    $idField = "id";
                    $idMethod = "getId";
                }

                $entities = $entityRepository->findBy([$idField => array_keys($entities)]);

                foreach ($entities as $entity) {
                    $entitiesToFetch[$class][$entity->$idMethod()->toRfc4122()] = $entity;
                }
            } else {
                /** @var LotRepository $entityRepository */
                $entityRepository = $this->entityManager->getRepository($class);

                if (!$entityRepository instanceof LotRepository) {
                    // If we switch from a Substance to a SubstanceLot type, existing entries will not change.
                    // $class will still reflect the class of the entry and $entityRepository will be the correct repository.
                    // But it will not be a LotRepository.
                    continue;
                }

                $entityClasses = [];

                foreach ($entities as $id => $entityClass) {
                    if (!array_key_exists($entityClass, $entityClasses)) {
                        $entityClasses[$entityClass] = [];
                    }

                    $entityClasses[$entityClass][] = $id;
                }

                foreach ($entityClasses as $entityClass => $listOfIds) {
                    $entities = $entityRepository->getLotsWithSubstance($entityClass, $listOfIds);

                    foreach ($entities as $entity) {
                        $entitiesToFetch[$class][$entity->getLot()->getId()->toRfc4122()] = $entity;
                    }
                }
            }
        }

        return $entitiesToFetch;
    }

    /**
     * @param Paginator<ExperimentalRunCondition> $conditions
     * @param array{str: array{str: object}} $entitiesToFetch
     * @return array<int, mixed>
     */
    public function createDataArray(Paginator $conditions, array $entitiesToFetch, ExperimentalDesign $design, ?int $maxRows=null): array
    {
        $data = [];

        $pushColumn = function (Collection $data, &$row) use ($entitiesToFetch, $design) {
            /** @var ExperimentalDatum<DatumEnum> $datum */
            foreach ($data as $datum) {
                $field = $design->getFields()->filter(fn (ExperimentalDesignField $field) => $field->getFormRow()->getFieldName() === $datum->getName())->first();

                if ($field === false) {
                    continue;
                }

                $key = $datum->getName();

                if ($datum->getType() === DatumEnum::EntityReference) {
                    /** @var Uuid $id */
                    [$id, $class] = $datum->getValue();

                    $value = $entitiesToFetch[$class][$id->toRfc4122()] ?? null;
                } else {
                    $value = $datum->getValue();
                }

                if (array_key_exists($key, $row)) {
                    if (!is_array($row[$key])) {
                        $row[$key] = [$row[$key]];
                    }

                    $row[$key][] = $value;
                } else {
                    $row[$key] = $value;
                }
            }
        };

        foreach ($conditions as $condition) {
            $row = [
                "set" => $condition,
                "run" => $condition->getExperimentalRun(),
            ];

            $pushColumn($condition->getExperimentalRun()->getData(), $row);
            $pushColumn($condition->getData(), $row);

            $row["data"] = [];

            foreach ($condition->getExperimentalRun()->getDataSets() as $dataSet) {
                if ($dataSet->getCondition() !== $condition) {
                    continue;
                }

                $subRow = [];
                $pushColumn($dataSet->getData(), $subRow);
                $row["data"][] = $subRow;

                $row["data"] = array_unique($row["data"], SORT_REGULAR);

                if ($maxRows !== null and count($row["data"]) === $maxRows) {
                    break;
                }
            }

            $data[] = $row;
        }

        return $data;
    }

    /**
     * @param null|array<string, "ASC"|"DESC"> $orderBy
     * @param array<string, mixed> $searchFields
     * @return int<0, max>
     */
    public function getPaginatedResultCount(
        ?array $orderBy = [],
        array $searchFields = [],
        ?ExperimentalDesign $design = null
    ): int {
        return (new Paginator($this->getResults($orderBy, $searchFields, $design)))->count();
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array<string, mixed> $searchFields
     * @param ExperimentalDesign|null $design
     * @return QueryBuilder
     */
    private function addSearchFields(
        QueryBuilder $queryBuilder,
        array $searchFields = [],
        ?ExperimentalDesign $design = null
    ): QueryBuilder {
        $searchService = $this->searchService;

        $expressions = $searchService->createExpressions($searchFields, fn (string $searchField, mixed $searchValue): mixed => match($searchField) {
            "design" => $searchService->searchWithUlid($queryBuilder, "exr.design", $searchValue->getId()->toRfc4122()),
            "run" => $searchService->searchWithUlid($queryBuilder, "exr", $searchValue->getid()->toRfc4122()),
            default => $this->addVariableSearchField($queryBuilder, $searchField, $searchValue, $design)
        });

        return $searchService->addExpressionsToSearchQuery($queryBuilder, $expressions);
    }

    private function addVariableSearchField(QueryBuilder $queryBuilder, string $searchField, mixed $searchValue, ExperimentalDesign $design): ?Func
    {
        // If the field name is coming from a composite, we need to adjust the search field.
        if (str_ends_with($searchField, "_lot")) {
            $searchField = substr($searchField, 0, -4);
            $suffix = "_lot";
        } elseif (str_ends_with($searchField, "_substance")) {
            $searchField = substr($searchField, 0, -10);
            $suffix = "_substance";
        } else {
            $suffix = "";
        }

        /** @var ExperimentalDesignField $fieldRow */
        $fieldRow = $design->getFields()->filter(fn (ExperimentalDesignField $field) => $field->getFormRow()->getFieldName() === $searchField)->first();

        $nameParamName = "name_".$fieldRow->getFormRow()->getFieldName();
        $referencesParamName = "references_".$fieldRow->getFormRow()->getFieldName();
        $abbreviation_suffix = $fieldRow->getFormRow()->getFieldName();

        if ($fieldRow->getFormRow()->getType() === FormRowTypeEnum::EntityType) {
            $queryBuilder->setParameter($nameParamName, $searchField);
            $queryBuilder->setParameter($referencesParamName, $searchValue);

            if ($suffix === "" or $suffix === "_lot") {
                return $queryBuilder->expr()->in(
                    "exrc.id",
                    $this->getSearchQueryBuilderForFieldType($fieldRow->getRole(), $abbreviation_suffix, $nameParamName)
                        ->andWhere("data$abbreviation_suffix.referenceUuid IN (:$referencesParamName)")
                        ->getDQL(),
                );
            } else {
                if (str_starts_with($fieldRow->getFormRow()->getConfiguration()["entityType"], Lot::class)) {
                    return $queryBuilder->expr()->in(
                        "exrc.id",
                        $this->getSearchQueryBuilderForFieldType($fieldRow->getRole(), $abbreviation_suffix, $nameParamName)
                            #->andWhere("data$abbreviation_suffix.referenceUuid IN (:$referencesParamName)")
                            ->andWhere($queryBuilder->expr()->in(
                                "data$abbreviation_suffix.referenceUuid",
                                $this->entityManager->createQueryBuilder()
                                    ->select("l$abbreviation_suffix.id")
                                    ->from(Substance::class, "s$abbreviation_suffix")
                                    ->join("s$abbreviation_suffix.lots", "l$abbreviation_suffix")
                                    ->where("s$abbreviation_suffix = :$referencesParamName")
                                    ->getDql(),
                            ))
                            ->getDQL(),
                    );
                }
            }
        } elseif ($fieldRow->getFormRow()->getType() === FormRowTypeEnum::TextType) {
            $queryBuilder->setParameter($nameParamName, $searchField);
            $queryBuilder->setParameter($referencesParamName, $this->searchService->parse($searchValue));

            return $queryBuilder->expr()->in(
                "exrc.id",
                $this->getSearchQueryBuilderForFieldType($fieldRow->getRole(), $abbreviation_suffix, $nameParamName)
                    ->andWhere("lower(convert_from(data$abbreviation_suffix.value, 'UTF-8')) LIKE lower(:$referencesParamName)")
                    ->getDQL(),
            );
        } elseif ($fieldRow->getFormRow()->getType() === FormRowTypeEnum::FloatType or $fieldRow->getFormRow()->getType() === FormRowTypeEnum::IntegerType) {
            if ($fieldRow->getFormRow()->getType() === FormRowTypeEnum::FloatType) {
                // Get datum type for correct encoding
                $datumType = match(FloatTypeEnum::from($fieldRow->getFormRow()->getConfiguration()["datatype_float"])) {
                    FloatTypeEnum::Float32 => DatumEnum::Float32,
                    FloatTypeEnum::Float64 => DatumEnum::Float64,
                };
            } else {
                $configuration = $fieldRow->getFormRow()->getConfiguration();
                $isUnsigned = $configuration["unsigned"];
                $datumType = match(IntegerTypeEnum::from($configuration["datatype_int"])) {
                    IntegerTypeEnum::Int8 => $isUnsigned ? DatumEnum::UInt8 : DatumEnum::Int8,
                    IntegerTypeEnum::Int16 => $isUnsigned ? DatumEnum::UInt16 : DatumEnum::Int16,
                    IntegerTypeEnum::Int32 => $isUnsigned ? DatumEnum::UInt32 : DatumEnum::Int32,
                    IntegerTypeEnum::Int64 => DatumEnum::Int64,
                };
            }

            // Normalize search value and encode
            $transformer = new NumberSearchTransformer();
            $codec = new ExperimentValueCodec($datumType);

            $searchValue = $transformer->transform($searchValue);
            $searchQuery = $this->getSearchQueryBuilderForFieldType($fieldRow->getRole(), $abbreviation_suffix, $nameParamName);
            $queryBuilder->setParameter($nameParamName, $searchField);

            // Determine the sign
            $minSign = $searchValue["type"][0] == "1" ? ">=" : ">";
            $maxSign = $searchValue["type"][1] == "1" ? "<=" : "<";

            if (isset($searchValue["min"]) and !is_nan($searchValue["min"])) {
                $minSearchValue = bin2hex($codec->encode($searchValue["min"]));
                $searchQuery->andWhere("data$abbreviation_suffix.value $minSign decode(:{$referencesParamName}min, 'hex')");
                $queryBuilder->setParameter($referencesParamName . "min", $minSearchValue);
            }
            if (isset($searchValue["max"]) and !is_nan($searchValue["max"])) {
                $maxSearchValue = bin2hex($codec->encode($searchValue["max"]));
                $searchQuery->andWhere("data$abbreviation_suffix.value $maxSign decode(:{$referencesParamName}max, 'hex')");
                $queryBuilder->setParameter($referencesParamName . "max", $maxSearchValue);
            }

            return $queryBuilder->expr()->in("exrc.id", $searchQuery->getDQL());
        }

        return null;
    }

    private function getSearchQueryBuilderForFieldType(ExperimentalFieldRole $role, string $suffix, string $nameParam): QueryBuilder
    {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->from(ExperimentalRunCondition::class, "nerc2$suffix")
            ->select("nerc2$suffix.id");

        $queryBuilder = match($role) {
            ExperimentalFieldRole::Top => $queryBuilder
                ->leftJoin("nerc2$suffix.experimentalRun", "nerc2r$suffix")
                ->leftJoin("nerc2r$suffix.data", "data$suffix"),

            ExperimentalFieldRole::Condition => $queryBuilder
                ->leftJoin("nerc2$suffix.data", "data$suffix"),

            ExperimentalFieldRole::Comparison, ExperimentalFieldRole::Datum => $queryBuilder
                ->leftJoin("nerc2$suffix.experimentalRun", "nerc2r$suffix")
                ->leftJoin("nerc2r$suffix.dataSets", "nerc2rds$suffix")
                ->leftJoin("nerc2rds$suffix.data", "data$suffix"),
        };

        $queryBuilder = $queryBuilder->where("data$suffix.name = :$nameParam");

        return $queryBuilder;
    }

    /**
     * @param mixed $value
     * @param FormRow $formRow
     * @return ($value is float ? string : "NAN")
     */
    public function convertFloatToString(mixed $value, FormRow $formRow): string
    {
        $configuration = $formRow->getConfiguration();

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

        return (string)$value;
    }

    /**
     * @param array<string, list<string>> $models
     */
    public function postUpdate(ExperimentalRun $run, array $models = []): void
    {
        $this->updateExpressionFields($run);
        $this->modelService->fit($run, $models);
        $this->updateModelFields($run);
    }

    public function updateExpressionFields(ExperimentalRun $run): void
    {
        $design = $run->getDesign();
        $expressionFields = $design->getFields()->filter(fn (ExperimentalDesignField $field) => $field->getFormRow()->getType() === FormRowTypeEnum::ExpressionType);

        foreach ($expressionFields as $expressionField) {
            $expression = $expressionField->getFormRow()->getConfiguration()["expression"] ?? null;
            $expressionFieldName = $expressionField->getFormRow()->getFieldName();

            if ($expressionField->getRole() === ExperimentalFieldRole::Condition) {
                foreach ($run->getConditions() as $condition) {
                    $environment = $this->modelService->getValueEnvironmentForCondition($condition);
                    $expressionLanguage = new ExpressionLanguage();

                    try {
                        $value = $expressionLanguage->evaluate($expression, $environment);
                    } catch (\Exception $e) {
                        $value = NAN;
                    }

                    $condition->addData((new ExperimentalDatum())->setName($expressionFieldName)->setType(DatumEnum::Float64)->setValue($value));
                }
            } elseif ($expressionField->getRole() === ExperimentalFieldRole::Datum) {
                foreach ($run->getDataSets() as $dataSet) {
                    $environment = $this->modelService->getValueEnvironmentForDataSet($dataSet);
                    $expressionLanguage = new ExpressionLanguage();

                    try {
                        $value = $expressionLanguage->evaluate($expression, $environment);
                    } catch (\Exception $e) {
                        $value = NAN;
                    }

                    $dataSet->addData((new ExperimentalDatum())->setName($expressionFieldName)->setType(DatumEnum::Float64)->setValue($value));
                }
            }
        }
    }

    public function updateModelFields(ExperimentalRun $run): void
    {
        $design = $run->getDesign();
        $modelFields = $design->getFields()->filter(fn (ExperimentalDesignField $field) => $field->getFormRow()->getType() === FormRowTypeEnum::ModelParameterType);

        $numberTransformer = new ScientificNumberTransformer(["NAN"], ["Inf"], ["-Inf"], "NAN", "Inf", "-Inf");

        foreach ($modelFields as $field) {
            $fieldConfig = $field->getFormRow()->getConfiguration();
            $fieldName = $field->getFormRow()->getFieldName();
            $model = $fieldConfig["model"];
            $param = $fieldConfig["param"];

            if (!$model or !$param) {
                $value = NAN;
            }

            if ($field->getRole() === ExperimentalFieldRole::Condition) {
                foreach ($run->getConditions() as $condition) {
                    $conditionModel = $condition->getModels()->findFirst(fn (int $index, ExperimentalModel $conditionModel) => $conditionModel->getName() === $model);

                    if (!$conditionModel) {
                        continue;
                    }

                    $modelResult = $conditionModel->getResult() ?? [];

                    $value = [
                        $modelResult["params"][$param]["value"] ?? NAN,
                        $numberTransformer->reverseTransform($modelResult["params"][$param]["stderr"] ?? NAN),
                        $numberTransformer->reverseTransform($modelResult["params"][$param]["ci"][0] ?? NAN),
                        $numberTransformer->reverseTransform($modelResult["params"][$param]["ci"][1] ?? NAN),
                    ];

                    $condition->addData((new ExperimentalDatum())->setName($fieldName)->setType(DatumEnum::ErrorFloat)->setValue($value));
                }
            } elseif ($field->getRole() === ExperimentalFieldRole::Top) {
                $values = [[], [], [], []];
                foreach ($run->getConditions() as $condition) {
                    $conditionModel = $condition->getModels()->findFirst(fn(int $index, ExperimentalModel $conditionModel) => $conditionModel->getName() === $model);

                    if (!$conditionModel) {
                        continue;
                    }

                    $modelResult = $conditionModel->getResult() ?? [];
                    $values[0][] = $modelResult["params"][$param]["value"] ?? NAN;
                    $values[1][] = $numberTransformer->reverseTransform($modelResult["params"][$param]["stderr"] ?? NAN);
                    $values[2][] = $numberTransformer->reverseTransform($modelResult["params"][$param]["ci"][0] ?? NAN);
                    $values[3][] = $numberTransformer->reverseTransform($modelResult["params"][$param]["ci"][1] ?? NAN);
                }

                $values = array_map(fn ($v) => array_sum($v) / count($v), $values);

                $run->addData((new ExperimentalDatum())->setName($fieldName)->setType(DatumEnum::ErrorFloat)->setValue($values));
            }
        }
    }
}
