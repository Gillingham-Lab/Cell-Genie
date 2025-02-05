<?php
declare(strict_types=1);

namespace App\Form\Form;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalModel;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Form\BasicType\FancyChoiceType;
use App\Form\BasicType\FormGroupType;
use App\Genie\Enums\FormRowTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @template TData
 * @extends AbstractType<TData>
 */
class ModelParameterTypeConfigurationType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->define("design")
            ->allowedTypes( ExperimentalDesign::class)
            ->required()
        ;
    }

    public function getParent()
    {
        return FormGroupType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $design = $options['design'];
        $models = $design->getModels();
        $modelChoices = [];
        foreach ($models as $model) {
            $modelChoices[$model->getName()] = $model->getName();
        }

        $builder
            ->add(
                "model", FancyChoiceType::class, [
                    "label" => "Model",
                    "required" => true,
                    "choices" => $modelChoices,
                    "allow_empty" => true,
                    "placeholder" => "Select a model",
                    "constraints" => [
                        new NotBlank(),
                    ],
                ],
            )
            ->add("param", TextType::class, [
                "label" => "Parameter",
                "required" => true,
                "disabled" => true,
                "constraints" => [
                    new NotBlank(),
                ],
            ])
        ;

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($builder, $options): void {
                $form = $event->getForm();
                $data = $event->getData();

                $this->modifyFormOnType($builder, $form, $data, $options);
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($builder, $options): void {
                $form = $event->getForm();
                $data = $event->getData();

                $this->modifyFormOnType($builder, $form, $data, $options);
            }
        );
    }

    /**
     * @param FormBuilderInterface<ModelParameterTypeConfigurationType> $builder
     * @param FormInterface<ModelParameterTypeConfigurationType> $form
     * @param array{model: null|string, param: null|string} $formData
     * @param array<string, mixed> $formOptions
     */
    private function modifyFormOnType(FormBuilderInterface $builder, FormInterface $form, null|array $formData, array $formOptions): void
    {
        if ($formData === null) {
            return;
        }

        if (!isset($formData["model"]) or $formData["model"] === null) {
            return;
        }

        /** @var ExperimentalDesign $design */
        $design = $formOptions['design'];
        $models = $design->getModels();
        $model = $models->findFirst(fn (int $key, ExperimentalModel $model) => $model->getName() === $formData["model"]);

        if ($model === null) {
            return;
        }

        $params = array_keys($model->getConfiguration()["params"] ?? []);

        $form->add("param", FancyChoiceType::class, [
            "label" => "Parameter",
            "required" => true,
            "allow_empty" => true,
            "empty_data" => null,
            "multiple" => false,
            "placeholder" => "Select a parameter",
            "choices" => array_combine($params, $params),
            "constraints" => [
                new NotBlank(),
            ],
        ]);
    }
}