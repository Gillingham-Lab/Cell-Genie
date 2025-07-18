<?php
declare(strict_types=1);

namespace App\Form\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalModel;
use App\Form\BasicType\FancyChoiceType;
use App\Form\BasicType\FormGroupType;
use App\Form\BasicType\ModelType;
use App\Form\CompositeType\XYFieldType;
use App\Service\Experiment\ExperimentalModelService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<ExperimentalModel>
 */
class ExperimentalModelType extends AbstractType
{
    public function __construct(
        private readonly ExperimentalModelService $modelService,
    ) {}

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => ExperimentalModel::class,
        ]);

        $resolver->define("design")
            ->allowedTypes(ExperimentalDesign::class, "null")
            ->default(null)
        ;

        $resolver->define("referenceModels")
            ->allowedTypes("string[]")
            ->default([]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $availableModels = $this->modelService->list();

        $builder
            ->add("model", ModelType::class, [])
            ->add("name", TextType::class, [
                "help" => "The model name is used to reference this model",
            ])
            ->add("referenceModel", FancyChoiceType::class, [
                "help" => "A reference model is used to overlay a reference line from a condition's references. Leave empty to not display external references.",
                "choices" => $options["referenceModels"],
                "allow_empty" => true,
                "required" => false,
                "empty_data" => null,
                "placeholder" => "Choose a model to use as a reference or leave empty",
            ])
            ->add(
                $builder->create("configuration", FormType::class, [
                    "label" => " ",
                    "allow_extra_fields" => true,
                    "row_attr" => [
                        "class" => "no-fieldset",
                    ],
                ])
                ->add("_xy", XYFieldType::class, [
                    "label" => "X/Y Fields",
                    "design" => $options["design"],
                    "required" => true,
                ])
                ->add(
                    $builder->create("params", FormGroupType::class, [
                        "label" => "Parameters",
                        "help" => "Set initial values for the parameters. These values will be used as default values 
                            for the model before optimisation is run. By setting min or max, you can limit the valid
                            bounds for the parameter fit. Turn off 'vary' to fix the parameter to the initial value.",
                    ]),
                )
                ->add(
                    $builder->create("evaluation", FormGroupType::class, [
                        "label" => "Additional settings",
                        "allow_extra_fields" => true,
                    ])
                    ->add("min", NumberType::class, [
                        "label" => "Minimum value for fit",
                        "empty_data" => null,
                    ])
                    ->add("max", NumberType::class, [
                        "label" => "Maximum value for fit",
                        "empty_data" => null,
                    ]),
                ),
            )
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, fn(FormEvent $event) => $this->onPreSetData($builder, $event, $availableModels, $options));
        $builder->addEventListener(FormEvents::PRE_SUBMIT, fn(FormEvent $event) => $this->onPreSubmit($builder, $event, $availableModels, $options));
    }

    /**
     * @param FormBuilderInterface<ExperimentalModel> $builder
     * @param FormEvent $event
     * @param array<string, mixed> $models
     * @param array<string, mixed> $options
     * @return void
     */
    public function onPreSetData(FormBuilderInterface $builder, FormEvent $event, array $models, array $options): void
    {
        $form = $event->getForm();
        $eventData = $event->getData();

        if ($eventData === null) {
            return;
        }

        if (is_array($eventData)) {
            $model = $eventData["model"] ?? null;
        } else {
            $model = $eventData->getModel();
        }

        if ($model === null || $model === "") {
            return;
        }

        $selectedModel = $models[$model];

        // Set default values
        if (is_array($eventData)) {
            $eventData["configuration"] = array_replace_recursive($selectedModel["defaults"] ?? [], $eventData["configuration"]);
        } else {
            $configuration = array_replace_recursive($selectedModel["defaults"] ?? [], $eventData->getConfiguration());
            $eventData->setConfiguration($configuration);
        }

        $event->setData($eventData);

        if ($eventData instanceof ExperimentalModel and $eventData->getId() !== null) {
            $form->add("model", ModelType::class, [
                "help" => $model["formula"] ?? null,
                "disabled" => true,
            ]);

            $form->add("name", TextType::class, [
                "disabled" => true,
            ]);
        }

        // Append form
        $this->appendForm($builder, $form, $selectedModel, $options);
    }

    /**
     * @param FormBuilderInterface<ExperimentalModel> $builder
     * @param FormEvent $event
     * @param array<string, mixed> $models
     * @param array<string, mixed> $options
     * @return void
     */
    public function onPreSubmit(FormBuilderInterface $builder, FormEvent $event, array $models, array $options): void
    {
        $form = $event->getForm();
        $eventData = $event->getData();

        if ($eventData === null) {
            return;
        }

        if (is_array($eventData)) {
            $model = $eventData["model"] ?? null;
        } else {
            $model = $eventData->getModel();
        }

        if ($model === null || $model === "") {
            return;
        }

        $selectedModel = $models[$model];

        // Set default values
        if (is_array($eventData)) {
            $eventData["configuration"] = array_replace_recursive($selectedModel["defaults"] ?? [], $eventData["configuration"]);

            if (empty($eventData["name"])) {
                $eventData["name"] = $model;
            }
        } else {
            $configuration = array_replace_recursive($selectedModel["defaults"] ?? [], $eventData->getConfiguration());
            $eventData->setConfiguration($configuration);
        }

        $event->setData($eventData);

        if ($eventData instanceof ExperimentalModel and $eventData->getId() !== null) {
            $form->add("model", ModelType::class, [
                "help" => $model["formula"] ?? null,
                "disabled" => true,
            ]);

            $form->add("name", TextType::class, [
                "disabled" => true,
            ]);
        }

        $this->appendForm($builder, $form, $selectedModel, $options);
    }

    /**
     * @param FormBuilderInterface<ExperimentalModel> $builder
     * @param FormInterface<ExperimentalModel> $form
     * @param array<string, mixed> $model
     * @param array<string, mixed> $options
     * @return void
     */
    public function appendForm(FormBuilderInterface $builder, FormInterface $form, array $model, array $options): void
    {
        $design = $options["design"];

        $configuration = $form->get("configuration");
        $configuration->add("_xy", XYFieldType::class, [
            "label" => "X/Y Fields",
            "design" => $options["design"],
            "x_label" => $model["param_help"]["x"]["label"] ?? null,
            "x_help" => $model["param_help"]["x"]["help"] ?? null,
            "y_label" => $model["param_help"]["y"]["label"] ?? null,
            "y_help" => $model["param_help"]["y"]["help"] ?? null,
        ]);

        $params = $form->get("configuration")->get("params");

        foreach ($model["param_names"] as $param) {
            $params->add($param, ExperimentalModelParamType::class, [
                "label" => $model["param_help"][$param]["label"] ?? $param,
                "help" => $model["param_help"][$param]["help"] ?? null,
                "environment" => ["ref", ... array_map(fn(ExperimentalDesignField $field) => $field->getFormRow()->getFieldName(), $design->getFields()->toArray())],
            ]);
        }
    }
}
