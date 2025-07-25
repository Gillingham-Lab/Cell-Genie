<?php
declare(strict_types=1);

namespace App\Twig\Components\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalModel;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\Table\Column;
use App\Entity\Table\ComponentColumn;
use App\Entity\Table\Table;
use App\Repository\Experiment\ExperimentalModelRepository;
use App\Repository\Experiment\ExperimentalRunConditionRepository;
use App\Service\Experiment\ExperimentalModelService;
use App\Twig\Components\UncertainFloat;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

/**
 * @phpstan-type AttributeStructure array{model: ExperimentalModel, run: ExperimentalRun}
 * @phpstan-type ValidatedAttributeStructure array{model: ExperimentalModel, run: ExperimentalRun, conditionModels: list<ExperimentalModel>}
 * @phpstan-import-type ArrayTableShape from Table
 */
#[AsTwigComponent]
class ModelView
{
    public ?ExperimentalModel $model;
    public ExperimentalRun $run;
    public ?ExperimentalRunCondition $condition;

    public bool $showParams = true;
    public bool $showWarnings = true;
    public bool $showErrors = true;
    public ?int $width = null;
    public bool $oneTraceOnly = false;

    public function __construct(
        private readonly ExperimentalRunConditionRepository $conditionRepository,
        private readonly ExperimentalModelRepository $modelRepository,
        private readonly ExperimentalModelService $modelService,
        private readonly Stopwatch $stopwatch,
    ) {}

    /**
     * @param AttributeStructure $attributes
     * @return ValidatedAttributeStructure
     */
    #[PreMount]
    public function preMount(array $attributes): array
    {
        $resolver = new OptionsResolver();
        $resolver
            ->define('model')
            ->allowedTypes(ExperimentalModel::class, "null")
            ->default(null)
        ;

        $resolver
            ->define('run')
            ->allowedTypes(ExperimentalRun::class)
            ->required();

        $resolver->define("condition")
            ->allowedTypes(ExperimentalRunCondition::class, "null")
            ->default(null)
        ;

        $resolver->define("width")->allowedTypes("int", "null")->default(null);
        $resolver->define("showParams")->allowedTypes("bool")->default(true);
        $resolver->define("showWarnings")->allowedTypes("bool")->default(true);
        $resolver->define("showErrors")->allowedTypes("bool")->default(true);
        $resolver->define("oneTraceOnly")->allowedTypes("bool")->default(false);

        return $resolver->resolve($attributes);
    }

    /**
     * @return ArrayCollection<int, array{condition: string, fit: ExperimentalModel, referenceFit: mixed[]}>
     */
    public function conditionModels(): ArrayCollection
    {
        $this->stopwatch->start("Component.ModelView.conditionModels");

        $conditionModels = new ArrayCollection();

        if ($this->model !== null) {
            if ($this->condition !== null) {
                $referenceModel = $this->model->getReferenceModel();

                $referenceConditions = $this->conditionRepository->getReferenceConditions($this->condition);
                $referenceFits = [];
                if ($referenceModel) {
                    $referenceFits = $this->modelRepository->getModelsForConditions($referenceModel, ... $referenceConditions);
                    $referenceFits = $this->modelService->getAverageFitResult(... $referenceFits);
                }

                $conditionModel = [
                    "condition" => $this->condition->getName(),
                    "fit" => $this->model,
                    "referenceFit" => $referenceFits,
                ];

                $conditionModels = new ArrayCollection([$conditionModel]);
            } else {
                $conditionModels = $this->run->getConditions()->map(
                    function (ExperimentalRunCondition $condition) {
                        $referenceConditions = $this->conditionRepository->getReferenceConditions($condition);

                        $modelFit = $condition->getModels()->findFirst(
                            fn(int $index, ExperimentalModel $model) => $model->getModel() === $this->model->getModel(),
                        );

                        $referenceModel = $modelFit?->getReferenceModel();
                        $referenceFits = [];
                        if ($referenceModel) {
                            $referenceFits = $this->modelRepository->getModelsForConditions($referenceModel, ... $referenceConditions);
                            $referenceFits = $this->modelService->getAverageFitResult(... $referenceFits);
                        }

                        return [
                            "condition" => $condition->getName(),
                            "fit" => $modelFit,
                            "referenceFit" => $referenceFits,
                        ];
                    },
                );

                $conditionModels = $conditionModels->filter(fn($x) => isset($x["condition"]));
            }
        }

        $this->stopwatch->stop("Component.ModelView.conditionModels");

        return $conditionModels;
    }


    /**
     * @param ArrayCollection<int, array{condition: string, fit: ExperimentalModel, referenceFit: mixed[]}> $conditionModels
     * @return ArrayTableShape
     */
    public function table(ArrayCollection $conditionModels): array
    {
        $this->stopwatch->start("Component.ModelView.table");

        $table = new Table();

        $modelConfiguration = $this->model?->getConfiguration();

        $data = [];
        $i = 0;

        if ($this->condition === null) {
            foreach ($this->run->getConditions() as $condition) {
                $data[] = ["condition" => $condition, "model" => $conditionModels[$i++]["fit"]];
            }
        } else {
            $data[] = ["condition" => $this->condition, "model" => $conditionModels[$i]["fit"]];
        }

        $table->setData($data);

        if ($modelConfiguration === null) {
            return $table->toArray();
        }

        $table->addColumn(new Column("Condition", fn($row) => $row["condition"]->getName()));

        foreach ($this->model->getConfiguration()["params"] as $param => $paramPresets) {
            $vary = $modelConfiguration["params"][$param]["vary"] ? "" : " (constant)";
            $table->addColumn(new ComponentColumn(
                $param . $vary,
                function ($row) use ($param) {
                    if ($row["model"] === null) {
                        return [
                            Datum::class, [
                                "datum" => null,
                            ],
                        ];
                    }

                    $modelResults = $row["model"]->getResult();

                    if (isset($modelResults["params"])) {
                        return [
                            UncertainFloat::class, [
                                "value" => $modelResults["params"][$param]["value"],
                                "stderr" => $modelResults["params"][$param]["stderr"],
                                "ci" => $modelResults["ci"],
                                "lowerCi" => $modelResults["params"][$param]["ci"][0] ?? null,
                                "upperCi" => $modelResults["params"][$param]["ci"][1] ?? null,
                            ],
                        ];
                    } else {
                        return [
                            Datum::class, [
                                "datum" => null,
                            ],
                        ];
                    }
                },
            ));
        }

        $this->stopwatch->start("Component.ModelView.table");

        return $table->toArray();
    }
}
