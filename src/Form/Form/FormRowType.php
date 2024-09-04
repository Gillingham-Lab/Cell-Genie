<?php
declare(strict_types=1);

namespace App\Form\Form;

use App\Entity\DoctrineEntity\Form\FormRow;
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

class FormRowType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => FormRow::class
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
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
        ;

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($builder): void {
                $form = $event->getForm();
                $data = $event->getData();

                $this->modifyFormOnType($builder, $form, $data);
            }
        );

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            function (FormEvent $event) use ($builder): void {
                $form = $event->getForm();
                $data = $event->getData();

                $this->modifyFormOnType($builder, $form, $data);
            }
        );
    }

    private function modifyFormOnType(FormBuilderInterface $builder, FormInterface $form, null|array|FormRow $formRow): void
    {
        if ($formRow === null) {
            return;
        }

        if (is_array($formRow)) {
            $type = $formRow["type"];
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
            default => null,
        };

        if ($formType !== null) {
            $form->add(
                "configuration",
                $formType,
                ["label" => "Type configuration"]
            );
        }
    }
}