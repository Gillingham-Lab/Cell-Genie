<?php
declare(strict_types=1);

namespace App\Form\Form;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Form\BasicType\FormGroupType;
use App\Genie\Enums\FormRowTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<FormRow>
 */
class FormRowType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => FormRow::class
        ]);

        $resolver->define("design")
            ->allowedTypes( ExperimentalDesign::class, "null")
            ->default(null)
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create("_type", FormGroupType::class, [
                    "inherit_data" => true,
                    "label" => "Common field settings",
                ])
                ->add("type", EnumType::class, [
                    "class" => FormRowTypeEnum::class,
                    "empty_data" => FormRowTypeEnum::TextType->value,
                    "required" => true,
                    "choice_label" => fn (FormRowTypeEnum $typeEnum) => $typeEnum->getLabel(),
                    "help" => "Changing the type while experiments are running can cause unexpected side effects.",
                ])
                ->add("label", TextType::class, [
                    "required" => true,
                ])
                ->add("help", TextareaType::class, [
                    "required" => false,
                ])
            )
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
     * @param FormBuilderInterface<FormRow> $builder
     * @param FormInterface<FormRow> $form
     * @param array{_type: array{type: value-of<FormRowTypeEnum>}}|FormRow|null $formRow
     * @param array<string, mixed> $formOptions
     */
    private function modifyFormOnType(FormBuilderInterface $builder, FormInterface $form, null|array|FormRow $formRow, array $formOptions): void
    {
        if ($formRow === null) {
            return;
        }

        if (is_array($formRow)) {
            $type = $formRow["_type"]["type"] ?? null;
        } else {
            $type = $formRow->getType()->value;
        }

        $formType = match ($type) {
            FormRowTypeEnum::TextType->value => TextTypeConfigurationType::class,
            FormRowTypeEnum::TextAreaType->value => TextAreaTypeConfigurationType::class,
            FormRowTypeEnum::IntegerType->value => IntegerTypeConfigurationType::class,
            FormRowTypeEnum::FloatType->value => FloatTypeConfigurationType::class,
            FormRowTypeEnum::EntityType->value => EntityTypeConfigurationType::class,
            FormRowTypeEnum::DateType->value => DateTypeConfigurationType::class,
            FormRowTypeEnum::ExpressionType->value => ExpressionTypeConfigurationType::class,
            FormRowTypeEnum::ModelParameterType->value => ModelParameterTypeConfigurationType::class,
            default => null,
        };

        if ($formType !== null) {
            $options = [
                "label" => "Type configuration",
            ];

            if (in_array($formType, [ExpressionTypeConfigurationType::class, ModelParameterTypeConfigurationType::class], true)) {
                $options["design"] = $formOptions["design"];
            }

            $form->add("configuration",  $formType, $options);
        }
    }
}