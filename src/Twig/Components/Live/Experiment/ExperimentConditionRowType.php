<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDatum;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRunCondition;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Form\LinkedEntityType;
use App\Form\ScientificNumberType;
use App\Genie\Enums\DatumEnum;
use App\Genie\Enums\FormRowTypeEnum;
use App\Service\Experiment\ExperimentalDataFormRowService;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

class ExperimentConditionRowType extends AbstractType
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private readonly ExperimentalDataFormRowService $formRowService,
    ) {

    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "fields" => null,
            "data_class" => ExperimentalRunCondition::class,
        ]);

        $resolver->setAllowedTypes("fields", Collection::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Add constant fields
        $builder
            ->add("name", TextType::class, [
                "label" => "Condition Name"
            ])
            ->add("control", CheckboxType::class, [
                "label" => "Control condition",
                "empty_data" => null,
                "required" => false,
            ])
        ;

        $dataField = $builder->create("data", FormType::class, [
            "mapped" => true,
        ]);

        /**
         * @var $datumTypeMap array{0: string, 1: string}
         */
        $datumTypeMap = [];

        /** @var ExperimentalDesignField $field */
        foreach ($options["fields"] as $field) {
            $name = $field->getFormRow()->getFieldName();
            $config = $this->getConfig($field);
            $config["inherit_data"] = true;

            $dataField->add($name, $config[0], $config[1]);
            $datumTypeMap[$field->getFormRow()->getFieldName()] = [$config[2], $field->getLabel()];
        }

        $entityManager = $this->entityManager;

        $builder->add($dataField);
        $builder
            ->get("data")
            ->addModelTransformer(new CallbackTransformer(
                function ($modelData) use ($datumTypeMap, $entityManager) {
                    if ($modelData === null) {
                        return [];
                    }

                    $normData = [];
                    /** @var ExperimentalDatum $datum */
                    foreach ($modelData as $datum) {
                        if ($datum->getType() === DatumEnum::EntityReference) {
                            [$id, $class] = $datum->getValue();
                            $instance = $entityManager->getRepository($class)->find($id);

                            if ($instance) {
                                $normData[$datum->getName()] = $instance;
                            }
                            #$normData[$datum->getName()] = null;
                        } else {
                            $normData[$datum->getName()] = $datum->getValue();
                        }
                    }

                    return $normData;
                },
                function ($normData) use ($datumTypeMap)
                {
                    $modelData = [];
                    foreach ($normData as $fieldName => $fieldValue) {
                        $datumType = $datumTypeMap[$fieldName][0];

                        if ($fieldValue === null) {
                            if ($datumType === DatumEnum::Float32 or $datumType === DatumEnum::Float64) {
                                $fieldValue = NAN;
                            } else {
                                continue;
                            }
                        }

                        $modelData[] = (new ExperimentalDatum())
                            ->setType($datumType)
                            ->setValue($fieldValue)
                            ->setName($fieldName);
                    }

                    return $modelData;
                }
            ));
    }

    /**
     * @param ExperimentalDesignField $field
     * @return array{0: string, 1: array, 2: DataTransformerInterface}
     */
    private function getConfig(ExperimentalDesignField $field): array
    {
        $formRow = $field->getFormRow();

        $return = match($field->getFormRow()->getType()) {
            FormRowTypeEnum::TextType => $this->getTextTypeConfig($formRow),
            FormRowTypeEnum::TextAreaType => $this->getTextAreaTypeConfig($formRow),
            FormRowTypeEnum::IntegerType => $this->getIntegerTypeConfig($formRow),
            FormRowTypeEnum::FloatType => $this->getFloatTypeConfig($formRow),
            FormRowTypeEnum::EntityType => $this->getEntityTypeConfig($formRow),
            default => [TextType::class, []]
        };

        $return[1]["label"] = $field->getLabel();

        if ($field->getFormRow()->getHelp()) {
            $return[1]["help"] = $field->getFormRow()->getHelp();
        }

        return $return;
    }

    private function getTextTypeConfig(FormRow $formRow): array
    {
        $options = [];
        $configuration = $formRow->getConfiguration();

        if ($configuration) {
            $lengthConstraint = new Length(
                min: $configuration["length_min"] > 0 ? $configuration["length_min"] : null,
                max: $configuration["length_max"] > 0 ? $configuration["length_max"] : null,
            );

            $options["constraints"] = [
                $lengthConstraint,
            ];
        }


        return [
            TextType::class,
            $options,
            DatumEnum::String,
        ];
    }

    private function getTextAreaTypeConfig(FormRow $formRow): array
    {
        [$type, $options, $datumType] = $this->getTextTypeConfig($formRow);
        return [
            TextareaType::class,
            $options,
            $datumType,
        ];
    }

    private function getIntegerTypeConfig(FormRow $formRow): array
    {
        $options = [];
        $configuration = $formRow->getConfiguration();
        $datumType = DatumEnum::Int;

        if ($configuration) {
            $bytes = $configuration["datatype_int"] ?? 2;
            $unsigned = $configuration["unsigned"] ?? false;

            if ($bytes !== 8) {
                if ($unsigned) {
                    $minimum = 0;
                    $maximum = 2**(8*$bytes)-1;
                } else {
                    $minimum = -2**(8*$bytes-1);
                    $maximum = 2**(8*$bytes-1)-1;
                }
            } else {
                $minimum = PHP_INT_MIN;
                $maximum = PHP_INT_MAX;
            }

            $datumType = match($bytes) {
                1 => $unsigned ? DatumEnum::UInt8 : DatumEnum::Int8,
                2 => $unsigned ? DatumEnum::UInt16 : DatumEnum::Int16,
                4 => $unsigned ? DatumEnum::UInt32 : DatumEnum::Int32,
                8 => DatumEnum::Int64,
            };

            $options["constraints"] = [
                new Range(min: $minimum, max: $maximum)
            ];
        }

        return [
            IntegerType::class,
            $options,
            $datumType,
        ];
    }

    private function getFloatTypeConfig(FormRow $formRow): array
    {
        $options = [];
        $configuration = $formRow->getConfiguration();

        $datumType = ($configuration["datatype_float"] ?? 1) === 1 ? DatumEnum::Float32 : DatumEnum::Float64;
        $inactiveFloatTypeLabel = $configuration["floattype_inactive_label"] ?? null;

        if ($inactiveFloatTypeLabel) {
            switch ($configuration["floattype_inactive"]) {
                case "NaN":
                    $options["nan_values"] = ["NaN", "NA", "<NA>", $inactiveFloatTypeLabel];
                    $options["na_value"] = $inactiveFloatTypeLabel;
                    break;

                case "-Inf":
                    $options["-inf_values"] = ["-Inf", $inactiveFloatTypeLabel];
                    $options["-inf_value"] = $inactiveFloatTypeLabel;
                    break;

                default:
                case "Inf":
                    $options["+inf_values"] = ["+Inf", "Inf", $inactiveFloatTypeLabel];
                    $options["+inf_value"] = $inactiveFloatTypeLabel;
                    break;
            }
        }

        return [
            ScientificNumberType::class,
            $options,
            $datumType,
        ];
    }

    private function getEntityTypeConfig(FormRow $formRow): array
    {
        $options = [];
        $configuration = $formRow->getConfiguration();

        $classes = explode("|", $configuration["entityType"]);

        $options["class"] = $classes[0];
        $options["empty_data"] = null;
        $options["attr"] = [
            "class" => "gin-fancy-select",
            "data-allow-empty" => "true",
        ];
        $options["required"] = false;
        $options["constraints"] = [
            new NotNull()
        ];

        if (count($classes) > 1) {
            if (is_subclass_of($classes[1], Substance::class)) {
                $query = $this->entityManager->getRepository($classes[1])->createQueryBuilder("s")
                    ->select("s")
                    ->leftJoin("s.lots", "l")
                    ->addSelect("l")
                    ->addOrderBy(method_exists($classes[1], "getNumber") ? "s.number": "s.shortName", "ASC")
                ;

                $entries = $query->getQuery()->getResult();

                $choices = [];
                foreach ($entries as $substance) {
                    $choices[(string)$substance] = $substance->getLots()->toArray();
                }

                $options["choices"] = $choices;
            }
        }

        return [
            EntityType::class,
            $options,
            DatumEnum::EntityReference,
        ];
    }
}