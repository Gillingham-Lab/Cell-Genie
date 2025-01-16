<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\ExperimentalCondition;
use App\Entity\ExperimentalMeasurement;
use App\Entity\InputType;
use App\Repository\LotRepository;
use App\Repository\Substance\ChemicalRepository;
use App\Repository\Substance\ProteinRepository;
use App\Repository\Substance\SubstanceRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @deprecated Old-style Experiment
 * @template TData
 * @extends AbstractType<TData>
 */
class ExperimentRunBaseType extends AbstractType
{
    /**
     * @param SubstanceRepository<Substance> $substanceRepository
     */
    public function __construct(
        protected ChemicalRepository  $chemicalRepository,
        protected ProteinRepository $proteinRepository,
        protected SubstanceRepository $substanceRepository,
        protected LotRepository $lotRepository,
    ) {
    }

    /**
     * @param FormBuilderInterface<TData> $formBuilder
     * @param InputType $inputType
     * @return void
     */
    protected function addInputTypeToForm(FormBuilderInterface $formBuilder, InputType $inputType): void
    {
        $type = match ($inputType->getType()) {
            InputType::INTEGER_TYPE => IntegerType::class,
            InputType::FLOAT_TYPE => NumberType::class,
            InputType::CHOICE_TYPE => ChoiceType::class,
            InputType::CHEMICAL_TYPE, InputType::PROTEIN_TYPE , InputType::SUBSTANCE_TYPE, InputType::LOT_TYPE => ChoiceType::class,
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
                "choice_label" => function($choice, $key, $value) {
                    return $value;
                },
                "expanded" => false,
                "multiple" => false,
            ];
        } elseif (
            $inputType->getType() === InputType::SUBSTANCE_TYPE or
            $inputType->getType() === InputType::PROTEIN_TYPE or
            $inputType->getType() === InputType::CHEMICAL_TYPE
        ) {
            $substances = $this->substanceRepository->findAll();

            $choices = [];
            $choiceIdToCategory = [];
            foreach ($substances as $substance) {
                if ($substance instanceof Antibody) {
                    $label = $substance->getNumber() . " | " . $substance->getShortName();
                } else {
                    $label = $substance->getShortName();
                }

                $value = $substance->getUlid()->toBase58();

                $choices[$label] = $value;
                $choiceIdToCategory[$value] = match (get_class($substance)) {
                    Antibody::class => "Antibody",
                    Chemical::class => "Chemical",
                    Oligo::class => "Oligo",
                    Protein::class => "Protein",
                    default => "Other",
                };
            }

            $options = [
                "empty_data" => null,
                "placeholder" => "None",
                "choices" => $choices,
                "expanded" => false,
                "multiple" => false,
                "group_by" => function ($choice) use ($choiceIdToCategory) {
                    return $choiceIdToCategory[$choice];
                },
                "attr" => [
                    "class" => "gin-fancy-select",
                ],
            ];
        } elseif ($inputType->getType() === InputType::LOT_TYPE) {
            $substanceLots = $this->substanceRepository->findAllSubstanceLots();

            $choices = [];
            $choiceIdToCategory = [];

            foreach ($substanceLots as $substanceLot) {
                $substance = $substanceLot->getSubstance();
                $lot = $substanceLot->getLot();

                if ($substance instanceof Antibody) {
                    $label = "{$substance->getNumber()}.{$lot->getNumber()}  | {$substance->getShortName()}";
                } else {
                    $label = "{$substance->getShortName()}.{$lot->getNumber()}";
                }

                $value = $lot->getId()->toBase58();

                $choices[$label] = $value;
                $choiceIdToCategory[$value] = match (get_class($substance)) {
                    Antibody::class => "Antibody",
                    Chemical::class => "Chemical",
                    Oligo::class => "Oligo",
                    Protein::class => "Protein",
                    default => "Other",
                };
            }

            $options = [
                "empty_data" => null,
                "placeholder" => "None",
                "choices" => $choices,
                "expanded" => false,
                "multiple" => false,
                "group_by" => function ($choice) use ($choiceIdToCategory) {
                    return $choiceIdToCategory[$choice];
                },
                "attr" => [
                    "class" => "gin-fancy-select",
                ],
            ];
        } elseif ($inputType->getType() === InputType::CHECK_TYPE) {
            $options = [
                "false_values" => ["no", "off", 0, 0.0, "n", "-", "N", null],
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
}