<?php
declare(strict_types=1);

namespace App\Service\Experiment;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDatum;
use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Entity\DoctrineEntity\Lot;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Form\BasicType\FancyChoiceType;
use App\Form\BasicType\FancyEntityType;
use App\Form\CropImageType;
use App\Form\ScientificNumberType;
use App\Genie\Enums\DatumEnum;
use App\Genie\Enums\FormRowTypeEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;

class ExperimentalDataFormRowService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {}

    /**
     * @template TData
     * @param FormBuilderInterface<TData> $builder
     * @param string $outerFormName
     * @param ExperimentalDesignField ...$designFields
     * @return FormBuilderInterface<TData>
     */
    public function createBuilder(
        FormBuilderInterface $builder,
        string $outerFormName,
        ExperimentalDesignField ... $designFields,
    ): FormBuilderInterface {
        $dataField = $builder->create($outerFormName, FormType::class, [
            "label" => " ",
            "mapped" => true,
            "required" => false,
        ]);

        $map = $this->addFields($dataField, ... $designFields);

        $builder->add($dataField);

        $this->addDataTransformer($builder, $outerFormName, $map);

        return $dataField;
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     * @param string $outerFormName
     * @param array<string, DatumEnum> $mappedFields
     */
    public function addDataTransformer(FormBuilderInterface $builder, string $outerFormName, array $mappedFields): void
    {
        $dataField = $builder->get($outerFormName);
        $dataField->addModelTransformer(new CallbackTransformer(
            fn($modelData) => $this->normalize($modelData),
            fn($normData) => $this->transformToModel($normData, $mappedFields),
        ));
    }

    /**
     * @param null|iterable<ExperimentalDatum<DatumEnum>> $modelData
     * @return array<string, mixed>
     */
    public function normalize(?iterable $modelData): array
    {
        if ($modelData === null) {
            return [];
        }

        $normData = [];
        foreach ($modelData as $datum) {
            if ($datum->getType() === DatumEnum::EntityReference) {
                /** @var class-string $class */
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

    /**
     * @param array<string, mixed> $normData
     * @param array<string, DatumEnum> $mappedFields
     * @return list<ExperimentalDatum<DatumEnum>>
     */
    public function transformToModel(array $normData, array $mappedFields): array
    {
        $modelData = [];
        foreach ($normData as $fieldName => $fieldValue) {
            if (!isset($mappedFields[$fieldName])) {
                continue;
            }

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
     * @param FormBuilderInterface<mixed> $builder
     * @return array<string, DatumEnum>
     */
    public function addFields(FormBuilderInterface $builder, ExperimentalDesignField ... $fields): array
    {
        $mappedFields = [];
        foreach ($fields as $field) {
            // Skip Expression and ModelParameterType, as they are not meant to be edited.
            if (in_array($field->getFormRow()->getType(), [FormRowTypeEnum::ExpressionType, FormRowTypeEnum::ModelParameterType])) {
                continue;
            }

            $datumType = $this->addField($builder, $field);
            $mappedFields[$field->getFormRow()->getFieldName()] = $datumType;
        }

        return $mappedFields;
    }

    /**
     * @param FormBuilderInterface<mixed> $builder
     * @return DatumEnum
     */
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
     * @return array{class-string<AbstractType<mixed>>, array<string, mixed>, DatumEnum}
     */
    public function getFieldConfiguration(FormRow $row): array
    {
        return match($row->getType()) {
            FormRowTypeEnum::TextType => $this->getTextTypeConfig($row),
            FormRowTypeEnum::TextAreaType => $this->getTextAreaTypeConfig($row),
            FormRowTypeEnum::IntegerType => $this->getIntegerTypeConfig($row),
            FormRowTypeEnum::FloatType => $this->getFloatTypeConfig($row),
            FormRowTypeEnum::EntityType => $this->getEntityTypeConfig($row),
            FormRowTypeEnum::DateType => $this->getDateTypeConfig($row),
            FormRowTypeEnum::ImageType => $this->getImageTypeConfig($row),
            FormRowTypeEnum::ModelParameterType, FormRowTypeEnum::ExpressionType => $this->getModelParameterTypeConfig($row),
            default => [TextType::class, [], DatumEnum::String],
        };
    }

    /**
     * @return array{class-string<TextType>, array{constraints: Constraint[]}, DatumEnum::String}
     */
    public function getTextTypeConfig(FormRow $row): array {
        $fieldConfig = [];
        $configuration = $row->getConfiguration();

        if ($configuration) {
            $constraints = [];
            if ($configuration["length_min"] > 0 or $configuration["length_max"] > 0) {
                $constraints[] = new Length(
                    min: $configuration["length_min"] > 0 ? $configuration["length_min"] : null,
                    max: $configuration["length_max"] > 0 ? $configuration["length_max"] : null,
                );
            }

            $fieldConfig["constraints"] = $constraints;
        }

        return [
            TextType::class,
            $fieldConfig,
            DatumEnum::String,
        ];
    }
    /**
     * @return array{class-string<TextareaType>, array{constraints: Constraint[]}, DatumEnum::String}
     */
    public function getTextAreaTypeConfig(FormRow $formRow): array
    {
        [$fieldType, $fieldConfig, $datumType] = $this->getTextTypeConfig($formRow);

        return [
            TextareaType::class,
            $fieldConfig,
            $datumType,
        ];
    }

    /**
     * @return array{class-string<IntegerType>, array{constraints: Constraint[]}, DatumEnum::UInt*|DatumEnum::Int*}
     */
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
                default => DatumEnum::Int64,
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

    /**
     * @return array{class-string<ScientificNumberType>, array<string, mixed>, DatumEnum::Float*}
     */
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

    /**
     * @return array{class-string<DateType>, array<string, mixed>, DatumEnum::Date}
     */
    public function getDateTypeConfig(FormRow $formRow): array
    {
        $fieldConfig = [
            "widget" => "single_text",
        ];

        return [
            DateType::class,
            $fieldConfig,
            DatumEnum::Date,
        ];
    }

    /**
     * @return array{class-string<FancyEntityType<mixed>|FancyChoiceType<mixed>>, array<string, mixed>, DatumEnum::EntityReference}
     */
    public function getEntityTypeConfig(FormRow $formRow): array
    {
        $configuration = $formRow->getConfiguration();
        $classes = explode("|", (string)$configuration["entityType"]);

        $fieldConfig = [
            "empty_data" => null,
            "allow_empty" => true,
            "required" => false,
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

                if (method_exists($classes[1], "getNumber")) {
                    $toStringCallback = fn($x, $lot) => $x->getNumber() . "." . $lot . " ({$x->getShortName()})";
                } else {
                    $toStringCallback = fn($x, $lot) => $lot . " - " . $x->getShortName();
                }

                $choices = [];
                foreach ($entries as $substance) {
                    $subChoices = [];
                    /** @var Lot $lot */
                    foreach ($substance->getLots() as $lot) {
                        $subChoices[$toStringCallback($substance, $lot)] = $lot;
                    }
                    $choices[(string)$substance] = $subChoices;
                }

                $fieldConfig["choices"] = $choices;
                $type = FancyChoiceType::class;
            } else {
                throw new \Exception("Double-classes are not supported outside of Substances.");
            }
        } else {
            $type = FancyEntityType::class;
            $fieldConfig["class"] = $classes[0] ?? Cell::class;
        }

        return [
            $type,
            $fieldConfig,
            DatumEnum::EntityReference,
        ];
    }

    /**
     * @return array{class-string<CropImageType>, array<string, mixed>, DatumEnum::Image}
     */
    public function getImageTypeConfig(FormRow $formRow): array
    {
        $fieldConfig = [];

        return [
            CropImageType::class,
            $fieldConfig,
            DatumEnum::Image,
        ];
    }

    /**
     * @param FormRow $formRow
     * @return array{class-string<TextType>, array<string, mixed>, DatumEnum::String}
     */
    public function getModelParameterTypeConfig(FormRow $formRow): array
    {
        return [
            TextType::class, [
                "disabled" => true,
            ],
            DatumEnum::String
        ];
    }
}