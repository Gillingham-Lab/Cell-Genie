<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\Experiment;
use App\Entity\ExperimentalCondition;
use App\Entity\ExperimentalMeasurement;
use App\Entity\ExperimentalRunWellFormEntity;
use App\Entity\InputType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentalRunWellType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var Experiment $experiment */
        $experiment = $options["experiment"];

        $builder
            ->add("wellName", TextType::class, [
                "label" => "Name of the well"
            ])
            ->add("isExternalStandard", CheckboxType::class, [
                "label" => false,
                "required" => false,
            ])
        ;

        // Get well conditions
        $conditionForm = null;

        /** @var ExperimentalCondition $condition */
        foreach ($experiment->getConditions() as $condition) {
            // skip general conditions
            if ($condition->isGeneral()) {
                continue;
            }

            if ($conditionForm === null) {
                $conditionForm = $builder->create("conditions", FormType::class, [
                    "label" => "Conditions",
                    "mapped" => false,
                    "inherit_data" => true,
                    "required" => false,
                ]);
            }

            $this->addInputTypeToForm($conditionForm, $condition);
        }

        if ($conditionForm !== null) {
            $builder->add($conditionForm);
        }

        // Get well measures
        $measurementForm = null;

        /** @var ExperimentalMeasurement $measurement */
        foreach ($experiment->getMeasurements() as $measurement) {
            if ($measurementForm === null) {
                $measurementForm = $builder->create("measurements", FormType::class, [
                    "label" => "Measurements",
                    "mapped" => false,
                    "inherit_data" => true,
                    "required" => false,
                ]);
            }

            $this->addInputTypeToForm($measurementForm, $measurement);
        }

        if ($measurementForm !== null) {
            $builder->add($measurementForm);
        }
    }

    protected function addInputTypeToForm(FormBuilderInterface $formBuilder, InputType $inputType)
    {
        $type = match ($inputType->getType()) {
            InputType::INTEGER_TYPE => IntegerType::class,
            InputType::FLOAT_TYPE => NumberType::class,
            InputType::CHOICE_TYPE => ChoiceType::class,
            InputType::CHECK_TYPE => CheckboxType::class,
            default => TextType::class,
        };

        $label = $inputType->getTitle();
        $istd = false;

        if ($inputType instanceof ExperimentalMeasurement and $inputType->isInternalStandard()) {
            $label .= " (ISTD)";
            $istd = true;
        }

        $baseOptions = [
            "label" => $label,
            "help" => $inputType->getDescription(),
        ];

        $options = [];

        // Prepare choices for CHOICE_TYPE
        // Should be made more flexible later.
        if ($inputType->getType() === InputType::CHOICE_TYPE) {
            $choices = explode(",", $inputType->getConfig());
            $choices = array_map("trim", $choices);

            $options = [
                "choices" => $choices,
                "expanded" => false,
                "multiple" => false,
            ];
        }

        if ($inputType instanceof ExperimentalMeasurement) {
            $formBuilder->add("measurement_{$inputType->getId()->toBase58()}", $type, array_merge($options, $baseOptions));
        } elseif ($inputType instanceof ExperimentalCondition) {
            $formBuilder->add("condition_{$inputType->getId()->toBase58()}", $type, array_merge($options, $baseOptions));
        } else {
            $formBuilder->add("value_{$inputType->getId()->toBase58()}", $type, array_merge($options, $baseOptions));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => ExperimentalRunWellFormEntity::class,
        ]);

        $resolver->setRequired([
            "experiment",
        ]);

        $resolver->setAllowedTypes("experiment", Experiment::class);
    }
}