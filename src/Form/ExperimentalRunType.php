<?php

namespace App\Form;

use App\Entity\Experiment;
use App\Entity\ExperimentalCondition;
use App\Entity\ExperimentalRunFormEntity;
use App\Entity\InputType;
use App\Repository\Substance\ChemicalRepository;
use App\Repository\Substance\ProteinRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentalRunType extends AbstractType
{
    public function __construct(
        private ChemicalRepository  $chemicalRepository,
        private ProteinRepository $proteinRepository,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
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

    protected function addInputTypeToForm(FormBuilderInterface $formBuilder, InputType $inputType)
    {
        $type = match ($inputType->getType()) {
            InputType::INTEGER_TYPE => IntegerType::class,
            InputType::FLOAT_TYPE => NumberType::class,
            InputType::CHOICE_TYPE => ChoiceType::class,
            InputType::CHEMICAL_TYPE, InputType::PROTEIN_TYPE => ChoiceType::class,
            InputType::CHECK_TYPE => CheckboxType::class,
            default => TextType::class,
        };

        $baseOptions = [
            "label" => $inputType->getTitle(),
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
        } elseif ($inputType->getType() === InputType::CHEMICAL_TYPE) {
            $chemicals = $this->chemicalRepository->findAll();

            $choices = [];
            foreach ($chemicals as $chemical) {
                $choices[$chemical->getShortName()] = $chemical->getUlid()->toBase58();
            }

            $options = [
                "choices" => $choices,
                "expanded" => false,
                "multiple" => false,
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-live-search" => "true"
                ],
            ];
        } elseif ($inputType->getType() === InputType::PROTEIN_TYPE) {
            $proteins = $this->proteinRepository->findAll();

            $choices = [];
            foreach ($proteins as $protein) {
                $choices[$protein->getShortName()] = $protein->getUlid()->toBase58();
            }

            $options = [
                "choices" => $choices,
                "expanded" => false,
                "multiple" => false,
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-live-search" => "true"
                ],
            ];
        }

        $formBuilder->add("condition_{$inputType->getId()->toBase58()}", $type, array_merge($options, $baseOptions));
    }

    public function configureOptions(OptionsResolver $resolver)
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