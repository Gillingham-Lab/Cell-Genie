<?php
declare(strict_types=1);

namespace App\Form\BasicType;

use App\Service\Experiment\ExperimentalModelService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<string>
 */
class ModelType extends AbstractType
{
    public function __construct(
        private readonly ExperimentalModelService $modelService,
    ) {

    }

    public function getParent()
    {
        return FancyChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $availableModels = $this->modelService->list();
        $modelsIdentifier = array_keys($availableModels);
        $modelsNames = array_map(fn (array $model) => $model["name"], $availableModels);

        $resolver->setDefaults([
            "choices" => array_combine($modelsNames, $modelsIdentifier),
            "allow_empty" => true,
            "required" => true,
            "placeholder" => "Select a model",
        ]);
    }
}
