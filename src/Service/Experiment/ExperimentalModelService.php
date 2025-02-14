<?php
declare(strict_types=1);

namespace App\Service\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalModel;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunDataSet;
use App\Genie\Enums\ExperimentalFieldRole;
use App\Genie\Enums\FormRowTypeEnum;
use App\Genie\Exceptions\FitException;
use App\Repository\Experiment\ExperimentalRunConditionRepository;
use Doctrine\ORM\EntityManagerInterface;
use ErrorException;
use Psr\Log\LoggerInterface;
use stdClass;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

readonly class ExperimentalModelService
{
    public function __construct(
        private string $projectDir,
        private string $pythonPath,
        private LoggerInterface $logger,
        private StopWatch $stopWatch,
        private TagAwareCacheInterface $cache,
        private EntityManagerInterface $entityManager,
        private ExperimentalRunConditionRepository $conditionRepository,
    ) {

    }

    public function getCommand(): string
    {
        if (str_starts_with($this->pythonPath, ".")) {
            return "{$this->projectDir}/{$this->pythonPath}python";
        } else {
            return "{$this->pythonPath}python";
        }
    }

    /**
     * @param literal-string $module
     * @param literal-string $params
     */
    public function run(string $module, string ... $params): string {
        $descriptorSpec = [
            ["pipe", "r"],
            ["pipe", "w"],
            ["pipe", "w"],
        ];
        $pipes = [];

        $proc = proc_open([
            $this->getCommand(),
            "-OO",
            "-m",
            "bin.fit",
            $module,
            ... $params,
        ], $descriptorSpec, $pipes, $this->projectDir);

        if (!is_resource($proc)) {
            throw new \RuntimeException("Failed read models");
        }

        $this->logger->debug("Running the fit module with parameters: " . implode(", ", $params));

        $content = trim(stream_get_contents($pipes[1]));
        $errorContent = trim(stream_get_contents($pipes[2]));

        proc_close($proc);

        if ($errorContent) {
            $this->logger->debug("Error content contains something: " . $errorContent);

            $lines = array_map(fn (string $str) => trim($str, characters: "\r"), explode("\n", $errorContent));

            $warnings = [];
            $errors = [];
            foreach ($lines as $line) {
                $matches = [];
                if (str_contains($line, "Warning")) {
                    preg_match("#[\w].*?:[\d].*?: [\w].*?: (.*)#", $line, $matches, PREG_UNMATCHED_AS_NULL);
                    if (count($matches) === 2) {
                        $warnings[] = $matches[1];
                        $this->logger->warning("Warning while running fit.py: " . $matches[1]);
                    } else {
                        $warnings[] = $line;
                        $this->logger->warning("Warning while running fit.py: " . $line);
                    }
                } elseif (str_contains($line, "Exception") or str_contains($line, "Error")) {
                    preg_match("#[\w].*?:[\d].*?: [\w.].*?: (.*)#", $line, $matches, PREG_UNMATCHED_AS_NULL);
                    if (count($matches) === 2) {
                        $errors[] = $matches[1];
                        $this->logger->critical("Error while running fit.py: " . $matches[1]);
                    } else {
                        $errors[] = $line;
                        $this->logger->critical("Error while running fit.py: " . $line);
                    }

                    $this->logger->critical("Fit parameters: " . implode(", ", $params));
                }
            }

            #dump($params, $content, $errorContent);
            throw new FitException($warnings, $errors, $content);
        }

        return $content;
    }

    /**
     * @return array<string, mixed>
     */
    public function list(): array {
        $this->stopWatch->start("ExperimentalModelService.list");

        $models = $this->cache->get("ExperimentalModelService.list", function (ItemInterface $item): array {
            $item->expiresAfter(600);
            $item->tag(["ExperimentalModelService", "cli"]);

            $models = $this->run("list");
            $models = json_decode($models, associative: true);
            return $models;
        });

        $this->stopWatch->stop("ExperimentalModelService.list");

        return $models;
    }

    /**
     * @param array<string, list<string>> $selectedModels
     */
    public function fit(ExperimentalRun $run, array $selectedModels = []): void
    {
        $this->stopWatch->start("ExperimentalModelService.fit");

        $design = $run->getDesign();
        $designModels = $design->getModels();
        $conditions = $run->getConditions();

        foreach ($conditions as $condition) {
            foreach ($designModels as $model) {
                $conditionModel = $condition->getModels()->findFirst(fn (int $index, ExperimentalModel $conditionModel) => $conditionModel->getName() === $model->getName());

                // If the condition appears in the selected model list, we restrict the models that will be fitted
                if (isset($selectedModels[$condition->getName()])) {
                    // If the model does not appear in the list, we skip the 'fitting'
                    if (!in_array($model->getModel(), $selectedModels[$condition->getName()])) {
                        // If a condition model already exist, we mark it for removal.
                        if ($conditionModel) {
                            $this->entityManager->remove($conditionModel);
                        }

                        continue;
                    }
                }

                if (!$conditionModel) {
                    $conditionModel = clone $model;
                    $condition->addModel($conditionModel);
                    $conditionModel->setParent($model);
                }

                // Overwrite the model with new parameters from the parent
                $conditionModel->setConfiguration($model->getConfiguration());

                try {
                    $result = $this->fitModel($conditionModel, $condition);
                } catch (FitException $e) {
                    $errors = $e->getErrors();
                    $warnings = $e->getWarnings();
                    $result = json_decode($e->getContent(), true);

                    if ($result === null) {
                        $result = [];
                    }

                    if (count($errors) > 0) {
                        $result["errors"] = $errors;
                    }

                    if (count($warnings) > 0) {
                        $result["warnings"] = $warnings;
                    }
                }

                $conditionModel->setResult($result);
            }
        }

        $this->stopWatch->stop("ExperimentalModelService.fit");
    }

    /**
     * @return array<string, mixed>
     */
    public function fitModel(ExperimentalModel $model, ExperimentalRunCondition $condition): array
    {
        $run = $condition->getExperimentalRun();
        $design = $run->getDesign();
        $modelConfig = $model->getConfiguration();

        $xValues = $this->getValuesForFit($condition, $modelConfig["x"]);
        $yValues = $this->getValuesForFit($condition, $modelConfig["y"]);

        if (count($xValues) !== count($yValues)) {
            $xCount = count($xValues);
            $yCount = count($yValues);
            throw new FitException(errors: ["Both x and y values must have the same length. Length of x is {$xCount}, y is {$yCount}"]);
        }

        $params = [];
        $environment = $this->getValueEnvironmentForCondition($condition);
        foreach ($modelConfig["params"] as $param => $paramConfig) {
            $params[$param] = [
                "initial" => $this->getValuesForFieldName($environment, $paramConfig["initial"]),
                "min" => $this->getValuesForFieldName($environment, $paramConfig["min"]),
                "max" => $this->getValuesForFieldName($environment, $paramConfig["max"]),
                "vary" => (bool)$paramConfig["vary"],
            ];
        }

        $reply = $this->runFit($model->getModel(), $xValues, $yValues, $params);

        return $reply;
    }

    /**
     * @param string $model
     * @param list<numeric> $xValues
     * @param list<numeric> $yValues
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    public function runFit(string $model, array $xValues, array $yValues, array $params): array
    {
        $fitConfiguration = [
            "x" => $xValues,
            "y" => $yValues,
            "params" => $params,
        ];

        return json_decode($this->run("fit", $model, json_encode($fitConfiguration)), associative: true);
    }

    /**
     * @return list<numeric>
     */
    public function getValuesForFit(ExperimentalRunCondition $condition, string $fieldName): array
    {
        $field = $condition->getExperimentalRun()->getDesign()->getFields()->findFirst(fn (int $index, ExperimentalDesignField $field) => $field->getFormRow()->getFieldName() === $fieldName);

        if (!$field) {
            return [];
        }

        $values = [];
        if ($field->getRole() === ExperimentalFieldRole::Datum) {
            $dataSets = $condition->getExperimentalRun()->getDataSets()->filter(fn (ExperimentalRunDataSet $dataSet) => $dataSet->getCondition() === $condition);
            foreach ($dataSets as $data) {
                if ($data->getData()->containsKey($fieldName)) {
                    $values[] = $data->getDatum($fieldName)->getValue();
                }
            }
        }

        return $values;
    }

    /**
     * @return list<string>
     */
    public function getValidEnvironment(ExperimentalDesign $design): array
    {
        $fields = $design->getFields();
        $environment = [];
        foreach ($fields as $field) {
            if (!in_array($field->getFormRow()->getType(), [FormRowTypeEnum::IntegerType, FormRowTypeEnum::FloatType])) {
                continue;
            }

            $fieldName = $field->getFormRow()->getFieldName();

            $environment[] = $fieldName;
        }

        return $environment;
    }

    /**
     * @return array<string, mixed>
     */
    public function getValueEnvironmentForCondition(ExperimentalRunCondition $condition, bool $getReference = true): array
    {
        $run = $condition->getExperimentalRun();
        $fields = $run->getDesign()->getFields();

        $environment = [];
        foreach ($fields as $field) {
            $fieldName = $field->getFormRow()->getFieldName();

            if (!in_array($field->getFormRow()->getType(), [FormRowTypeEnum::IntegerType, FormRowTypeEnum::FloatType])) {
                if (!($getReference === false and in_array($field->getFormRow()->getType(), [FormRowTypeEnum::ModelParameterType]))) {
                    continue;
                }
            }

            if ($field->getRole() === ExperimentalFieldRole::Datum) {
                $dataSets = $condition->getExperimentalRun()->getDataSets()->filter(fn (ExperimentalRunDataSet $dataSet) => $dataSet->getCondition() === $condition and $dataSet->getControlCondition() === null);
                $values = array_map(fn (ExperimentalRunDataSet $dataSet) => $dataSet->getData()->containsKey($fieldName) ? $dataSet->getDatum($fieldName)->getValue() : null, $dataSets->toArray());
                $environment[$fieldName] = $values;
            } elseif ($field->getRole() === ExperimentalFieldRole::Top) {
                $environment[$fieldName] = $run->getData()->containsKey($fieldName) ? $run->getDatum($fieldName)->getValue() : null;
            } elseif ($field->getRole() === ExperimentalFieldRole::Condition) {
                $environment[$fieldName] = $condition->getData()->containsKey($fieldName) ? $condition->getDatum($fieldName)->getValue() : null;
            }

            // Model parameter types return 4 values - value, stderr, lower_ci and upper_ci. We only need the value. Indecies start at 1 though!
            if ($field->getFormRow()->getType() === FormRowTypeEnum::ModelParameterType) {
                if (is_array($environment[$fieldName])) {
                    $environment[$fieldName] = $environment[$fieldName][1];
                }
            }
        }

        if ($getReference) {
            $environment["ref"] = $this->getReferenceValueEnvironmentForCondition($condition);
        }

        return $environment;
    }
  
    /**
     * @return object
     */
    public function getReferenceValueEnvironmentForCondition(ExperimentalRunCondition $condition): object
    {
        $references = $this->conditionRepository->getReferenceConditions($condition);
        $values = [];
        foreach ($references as $reference) {
            $referenceValues = $this->getValueEnvironmentForCondition($reference, false);

            foreach ($referenceValues as $key => $referenceValue) {
                if (!isset($values[$key])) {
                    $values[$key] = [];
                }

                if (
                    $referenceValue === null or $referenceValue === "NAN" or $referenceValue === "+Inf" or $referenceValue === "-Inf"
                        or (is_array($referenceValue) and $referenceValue === [])
                ) {
                    continue;
                }

                $values[$key][] = $referenceValue;
            }
        }

        $values = array_map(function (array $value) {
            if (count($value) > 0 and is_array($value[0])) {
                $value = array_map(fn ($v) => array_sum($v)/count($v), $value);
            }

            if (count($value) === 0) {
                return null;
            }

            try {
                $value = array_filter($value, fn ($v) => !(is_nan($v) or is_infinite($v)));
                return array_sum($value)/count($value);
            } catch (ErrorException $e) {
                return 0;
            }

        }, $values);

        $object = new class {
            public function __get(string $name): null
            {
                return null;
            }
        };

        foreach ($values as $key => $value) {
            $object->{$key} = $value;
        }

        return $object;
    }

    /**
     * @return array<string, mixed>
     */
    public function getValueEnvironmentForDataSet(ExperimentalRunDataSet $dataSet): array
    {
        $run = $dataSet->getExperiment();
        $fields = $run->getDesign()->getFields();

        $environment = [];
        foreach ($fields as $field) {
            $fieldName = $field->getFormRow()->getFieldName();

            if (!in_array($field->getFormRow()->getType(), [FormRowTypeEnum::IntegerType, FormRowTypeEnum::FloatType])) {
                continue;
            }

            if ($field->getRole() === ExperimentalFieldRole::Datum) {
                $value = $dataSet->getData()->containsKey($fieldName) ? $dataSet->getDatum($fieldName)->getValue() : null;
            } elseif ($field->getRole() === ExperimentalFieldRole::Top) {
                $value = $run->getData()->containsKey($fieldName) ? $run->getDatum($fieldName)->getValue() : null;
            } elseif ($field->getRole() === ExperimentalFieldRole::Condition) {
                $value = $dataSet->getCondition()->getData()->containsKey($fieldName) ? $dataSet->getCondition()->getDatum($fieldName)->getValue() : null;
            } else {
                $value = null;
            }

            $environment[$fieldName] = $value;
        }

        return $environment;
    }

    /**
     * @param array<string, mixed> $environment
     * @return float|list<numeric>
     */
    public function getValuesForFieldName(array $environment, mixed $value): null|float|array
    {
        if ($value === null) {
            return null;
        } elseif (is_float($value) || is_int($value)) {
            return $value;
        } else {
            $expression = new ExpressionLanguage();
            return $expression->evaluate($value, $environment);
        }
    }

    /**
     * @param ExperimentalModel ...$models
     * @return array<string, mixed>
     */
    public function getAverageFitResult(ExperimentalModel ...$models): array
    {
        if (count($models) === 0) {
            return [];
        }

        $average = [
            "x" => [],
            "y" => [],
            "params" => [],
            "evaluation" => [
                "min" => null,
                "max" => null,
            ],
        ];

        foreach ($models as $model) {
            $result = $model->getResult();
            array_push($average["x"], ...$result["x"]);
            array_push($average["y"], ...$result["y"]);
            $average["params"] = array_merge_recursive($average["params"], $result["params"]);
            $average["evaluation"] = array_merge_recursive($average["evaluation"], $result["evaluation"]);
        }

        sort($average["x"]);
        sort($average["y"]);

        foreach ($average["params"] as $param => $value) {
            $average["params"][$param] = array_sum($value["value"]) / count($value["value"]);
        }

        $average["evaluation"]["N"] = max($average["evaluation"]["N"]);
        $average["evaluation"]["spacing"] = $average["evaluation"]["spacing"][0];

        // This is always true but for now required from PHP stan.
        if (count($average["x"]) > 0) {
            $average["evaluation"]["min"] = min($average["x"]);
            $average["evaluation"]["max"] = max($average["x"]);
        }

        $this->stopWatch->start("ExperimentalModelService.eval");

        try {
            $reply = $this->run("eval", $models[0]->getModel(), json_encode($average));
        } catch (FitException $e) {
            $reply = $e->getContent();
        }

        $reply = json_decode($reply, true);

        $this->stopWatch->stop("ExperimentalModelService.eval");

        return [
            "x" => $average["x"],
            "y" => $average["y"],
            "fit" => $reply,
        ];
    }
}