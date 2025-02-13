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
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\Attribute\PreMount;

/**
 * @phpstan-type AttributeStructure array{model: ExperimentalModel, run: ExperimentalRun}
 * @phpstan-type ValidatedAttributeStructure array{model: ExperimentalModel, run: ExperimentalRun, conditionModels: list<ExperimentalModel>}
 * @phpstan-import-type ArrayTableShape from Table
 */
#[AsTwigComponent]
class ModelView
{
    public ExperimentalModel $model;
    public ExperimentalRun $run;
    /** @var ArrayCollection<int, array{condition: string, fit: ExperimentalModel}>  */
    public ArrayCollection $conditionModels;

    public function __construct(
        private readonly ExperimentalRunConditionRepository $conditionRepository,
        private readonly ExperimentalModelRepository $modelRepository,
        private readonly ExperimentalModelService $modelService,
    ) {

    }

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
            ->allowedTypes(ExperimentalModel::class)
            ->required();

        $resolver
            ->define('run')
            ->allowedTypes(ExperimentalRun::class)
            ->required();

        $attributes = $resolver->resolve($attributes);

        $attributes["conditionModels"] = $attributes["run"]->getConditions()->map(
            function (ExperimentalRunCondition $condition) use ($attributes) {
                $referenceConditions = $this->conditionRepository->getReferenceConditions($condition);

                $modelFit = $condition->getModels()->findFirst(
                    fn (int $index, ExperimentalModel $model) => $model->getModel() === $attributes["model"]->getModel()
                );

                $referenceModel = $modelFit->getReferenceModel();
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
            }
        );

        return $attributes;
    }


    /**
     * @return ArrayTableShape
     */
    public function table(): array
    {
        $modelConfiguration = $this->model->getConfiguration();

        $data = [];
        $i = 0;
        foreach ($this->run->getConditions() as $condition) {
            $data[] = ["condition" => $condition, "model" => $this->conditionModels[$i++]["fit"]];
        }

        $table = new Table();
        $table->setData($data);

        $table->addColumn(new Column("Condition", fn ($row) => $row["condition"]->getName()));

        foreach ($this->model->getConfiguration()["params"] as $param => $paramPresets) {
            $vary = $modelConfiguration["params"][$param]["vary"] ? "" : " (constant)";
            $table->addColumn(new ComponentColumn(
                $param . $vary, function ($row) use ($param) {
                    if ($row["model"] === null) {
                        return [
                            Datum::class, [
                                "datum" => null,
                            ]
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
                            ]
                        ];
                    } else {
                        return [
                            Datum::class, [
                                "datum" => null,
                            ]
                        ];
                    }
                }
            ));
        }

        return $table->toArray();
    }
}