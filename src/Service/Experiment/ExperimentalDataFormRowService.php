<?php
declare(strict_types=1);

namespace App\Service\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDatum;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Form\ScientificNumberType;
use App\Genie\Enums\DatumEnum;
use App\Genie\Enums\FormRowTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Range;

class ExperimentalDataFormRowService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function createBuilder(
        FormBuilderInterface $builder,
        string $outerFormName,
        ExperimentalDesignField ... $designFields,
    ): FormBuilderInterface {
        $dataField = $builder->create($outerFormName, FormType::class, [
            "mapped" => true,
        ]);

        $map = $this->addFields($dataField, $designFields);

        $builder->add($dataField);

        $this->addDataTransformer($builder, $outerFormName, $map);

        return $dataField;
    }

    public function addDataTransformer(FormBuilderInterface $builder, string $outerFormName, array $mappedFields): void
    {
        $dataField = $builder->get($outerFormName);
        $dataField->addModelTransformer(new CallbackTransformer(
            fn($modelData) => $this->normalize($modelData),
            fn($normData) => $this->transformToModel($normData, $mappedFields),
        ));
    }

    public function normalize(?iterable $modelData): array
    {
        if ($modelData === null) {
            return [];
        }

        $normData = [];
        /** @var ExperimentalDatum $datum */
        foreach ($modelData as $datum) {
            if ($datum->getType() === DatumEnum::EntityReference) {
                [$id, $class] = $datum->getValue();
                $instance = $this->entityManager->getRepository($class)->find($id);

                if ($instance) {
                    $normData[$datum->getName()] = $instance;
                }
            } else {
                $normData[$datum->getName()] = $datum->getValue();
            }
        }

        return $normData;
    }

    public function transformToModel($normData, $mappedFields): array
    {
        $modelData = [];
        foreach ($normData as $fieldName => $fieldValue) {
            $datumType = $mappedFields[$fieldName];

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

    /**
     * @param FormBuilderInterface $builder
     * @param ExperimentalDesignField[] $fields
     * @return array<string>
     */
    public function addFields(FormBuilderInterface $builder, array $fields): array
    {
        $mappedFields = [];
        foreach ($fields as $field) {
            $datumType = $this->addField($builder, $field);
            $mappedFields[$field->getFormRow()->getFieldName()] = $datumType;
        }

        return $mappedFields;
    }

    public function addField(FormBuilderInterface $builder, ExperimentalDesignField $field): DatumEnum
    {
        $fieldRow = $field->getFormRow();
        $fieldName = $fieldRow->getFieldName();
        [$fieldType, $fieldConfig, $datumType] = $this->getFieldConfiguration($fieldRow);
        $fieldConfig = [
            ... $fieldConfig,
            "label" => $fieldRow->getLabel(),
            "help" => $fieldRow->getHelp(),
        ];

        $builder->add($fieldName, $fieldType, $fieldConfig);

        return $datumType;
    }

    /**
     * @param ExperimentalDesignField $field
     * @return array{0: string, 1: array, 2: DatumEnum}
     */
    public function getFieldConfiguration(FormRow $row): array
    {
        return match($row->getType()) {
            FormRowTypeEnum::TextType => $this->getTextTypeConfig($row),
            FormRowTypeEnum::TextAreaType => $this->getTextAreaTypeConfig($row),
            FormRowTypeEnum::IntegerType => $this->getIntegerTypeConfig($row),
            FormRowTypeEnum::FloatType => $this->getFloatTypeConfig($row),
            FormRowTypeEnum::EntityType => $this->getEntityTypeConfig($row),
            default => [TextType::class, [], DatumEnum::String],
        };
    }

    public function getTextTypeConfig(FormRow $row) {
        $fieldConfig = [];
        $configuration = $row->getConfiguration();

        if ($configuration) {
            $lengthConstraint = new Length(
                min: $configuration["length_min"] > 0 ? $configuration["length_min"] : null,
                max: $configuration["length_max"] > 0 ? $configuration["length_max"] : null,
            );

            $fieldConfig["constraints"] = [
                $lengthConstraint,
            ];
        }

        return [
            TextType::class,
            $fieldConfig,
            DatumEnum::String,
        ];
    }

    public function getTextAreaTypeConfig(FormRow $formRow): array
    {
        [$fieldType, $fieldConfig, $datumType] = $this->getTextTypeConfig($formRow);
        return [
            TextareaType::class,
            $fieldConfig,
            $datumType,
        ];
    }

    public function getIntegerTypeConfig(FormRow $row): array
    {
        $fieldConfig = [];
        $configuration = $row->getConfiguration();
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

            $fieldConfig["constraints"] = [
                new Range(min: $minimum, max: $maximum)
            ];
        }

        return [
            IntegerType::class,
            $fieldConfig,
            $datumType,
        ];
    }

    public function getFloatTypeConfig(FormRow $row): array
    {
        $fieldConfig = [];
        $configuration = $row->getConfiguration();

        $datumType = ($configuration["datatype_float"] ?? 1) === 1 ? DatumEnum::Float32 : DatumEnum::Float64;
        $inactiveFloatTypeLabel = $configuration["floattype_inactive_label"] ?? null;

        if ($inactiveFloatTypeLabel) {
            switch ($configuration["floattype_inactive"]) {
                case "NaN":
                    $fieldConfig["nan_values"] = ["NaN", "NA", "<NA>", $inactiveFloatTypeLabel];
                    $fieldConfig["na_value"] = $inactiveFloatTypeLabel;
                    break;

                case "-Inf":
                    $fieldConfig["-inf_values"] = ["-Inf", $inactiveFloatTypeLabel];
                    $fieldConfig["-inf_value"] = $inactiveFloatTypeLabel;
                    break;

                default:
                case "Inf":
                    $fieldConfig["+inf_values"] = ["+Inf", "Inf", $inactiveFloatTypeLabel];
                    $fieldConfig["+inf_value"] = $inactiveFloatTypeLabel;
                    break;
            }
        }

        return [
            ScientificNumberType::class,
            $fieldConfig,
            $datumType,
        ];
    }

    public function getEntityTypeConfig(FormRow $formRow): array
    {
        $configuration = $formRow->getConfiguration();
        $classes = explode("|", $configuration["entityType"]);

        $fieldConfig = [
            "class" => $classes[0],
            "empty_data" => null,
            "attr" => [
                "class" => "gin-fancy-select",
                "data-allow-empty" => "true",
            ],
            "required" => false,
            "constraints" => [
                new NotNull()
            ],
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

                $fieldConfig["choices"] = $choices;
            }
        }

        return [
            EntityType::class,
            $fieldConfig,
            DatumEnum::EntityReference,
        ];
    }
}