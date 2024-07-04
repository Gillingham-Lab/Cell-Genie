<?php

namespace App\Form;

use App\Entity\Experiment;
use App\Entity\ExperimentalCondition;
use App\Entity\ExperimentalRunFormEntity;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentalRunType extends ExperimentRunBaseType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Experiment $experiment */
        $experiment = $options["experiment"];

        // Standard fields
        $builder
            ->add("name", TextType::class, [
                "label" => "Run name",
                "help" => "Must be unique per experiment.",
            ])
            ->add("numberOfWells", IntegerType::class, [
                "label" => "Number of wells",
                "help" => "Number of experimental runs per experiment. Cannot currently be changed after saving.",
                "disabled" => $options["disable_numberOfWells"],
            ])
        ;

        // Add general conditions to experimental run
        $conditionForm = null;

        /** @var ExperimentalCondition $condition */
        foreach($experiment->getConditions() as $condition) {
            if ($condition->isGeneral() === false) {
                continue;
            }

            if ($conditionForm === null) {
                $conditionForm = $builder->create("conditions", FormType::class, [
                    "label" => "Conditions",
                    "mapped" => false,
                    "inherit_data" => true,
                ]);
            }

            $this->addInputTypeToForm($conditionForm, $condition);
        }

        if ($conditionForm !== null) {
            $builder->add($conditionForm);
        }

        if ($options["save_button"] === true) {
            $builder->add("save", SubmitType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => ExperimentalRunFormEntity::class,
            "save_button" => false,
            "disable_numberOfWells" => false,
        ]);

        $resolver->setRequired([
            "experiment",
        ]);

        $resolver->setAllowedTypes("experiment", Experiment::class);
        $resolver->setAllowedTypes("save_button", "bool");
        $resolver->setAllowedTypes("disable_numberOfWells", "bool");
    }
}