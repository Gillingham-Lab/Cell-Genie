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
use App\Genie\Exceptions\GinException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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

        $content = trim(stream_get_contents($pipes[1]));
        $errorContent = trim(stream_get_contents($pipes[2]));

        proc_close($proc);

        if ($errorContent) {
            $lines = array_map(fn (string $str) => trim($str, characters: "\r"), explode("\n", $errorContent));

            $warnings = [];
            $errors = [];
            foreach ($lines as $line) {
                $matches = [];
                if (str_contains($line, "Warning")) {
                    preg_match("#[\w].*?:[\d].*?: [\w].*?: (.*)#", $line, $matches, PREG_UNMATCHED_AS_NULL);
                    $this->logger->warning("Warning while running fit.py: " . $matches[1]);
                    $warnings[] = $matches[1];
                } elseif (str_contains($line, "Exception") or str_contains($line, "Error")) {
                    preg_match("#[\w].*?:[\d].*?: [\w].*?: (.*)#", $line, $matches, PREG_UNMATCHED_AS_NULL);
                    $errors[] = $matches[1];
                    $this->logger->critical("Error while running fit.py: " . $matches[1]);
                }
            }

            dump($params, $content, $errorContent);
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

    public function fit(ExperimentalRun $run): void
    {
        $this->stopWatch->start("ExperimentalModelService.fit");

        $design = $run->getDesign();
        $designModels = $design->getModels();
        $conditions = $run->getConditions();

        foreach ($conditions as $condition) {
            foreach ($designModels as $model) {
                $conditionModel = $condition->getModels()->findFirst(fn (int $index, ExperimentalModel $conditionModel) => $conditionModel->getName() === $model->getName());
                if (!$conditionModel) {
                    $conditionModel = clone $model;
                    $condition->addModel($conditionModel);
                }

                try {
                    $result = $this->fitModel($conditionModel, $condition);
                } catch (FitException $e) {
                    $errors = $e->getErrors();
                    $warnings = $e->getWarnings();
                    $result = json_decode($e->getMessage(), true);

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
            throw new GinException("Both x and y values must have the same length. Length of x is {$xCount}, y is {$yCount}");
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
                $values[] = $data->getDatum($fieldName)->getValue();
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
    public function getValueEnvironmentForCondition(ExperimentalRunCondition $condition): array
    {
        $run = $condition->getExperimentalRun();
        $fields = $run->getDesign()->getFields();

        $environment = [];
        foreach ($fields as $field) {
            $fieldName = $field->getFormRow()->getFieldName();

            if (!in_array($field->getFormRow()->getType(), [FormRowTypeEnum::IntegerType, FormRowTypeEnum::FloatType])) {
                continue;
            }

            if ($field->getRole() === ExperimentalFieldRole::Datum) {
                $dataSets = $condition->getExperimentalRun()->getDataSets()->filter(fn (ExperimentalRunDataSet $dataSet) => $dataSet->getCondition() === $condition and $dataSet->getControlCondition() === null);
                $values = array_map(fn (ExperimentalRunDataSet $dataSet) => $dataSet->getData()->containsKey($fieldName) ? $dataSet->getDatum($fieldName)?->getValue() : null, $dataSets->toArray());
                $environment[$fieldName] = $values;
            } elseif ($field->getRole() === ExperimentalFieldRole::Top) {
                $environment[$fieldName] = $run->getData()->containsKey($fieldName) ? $run->getDatum($fieldName)->getValue() : null;
            } elseif ($field->getRole() === ExperimentalFieldRole::Condition) {
                $environment[$fieldName] = $condition->getData()->containsKey($fieldName) ? $condition->getDatum($fieldName)->getValue() : null;
            }
        }

        return $environment;
    }

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
}