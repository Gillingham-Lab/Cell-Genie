<?php
declare(strict_types=1);

namespace App\Form\Experiment;

use App\Entity\DoctrineEntity\Form\FormRow;
use App\Form\BasicType\FancyChoiceType;
use App\Form\Search\NumberSearchType;
use App\Genie\Enums\FormRowTypeEnum;
use App\Service\Experiment\ExperimentalDataFormRowService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @extends AbstractType<array>
 */
class ExperimentalSearchDataType extends AbstractType
{
    public function __construct(
        private readonly ExperimentalDataFormRowService $formRowService,
    ) {}

    /**
     * @param array{fields: Collection<int, FormRow>|array<int, FormRow>, fieldChoices: array<string, string[]>} $data
     * @return array{fields: array<int, string>, fieldChoices: array<string, string[]>}
     */
    public static function serialize(SerializerInterface $serializer, array $data): array
    {
        return [
            "fields" => array_map(fn($elm) => $serializer->serialize($elm, "json"), is_array($data["fields"]) ? $data["fields"] : $data["fields"]->toArray()),
            "fieldChoices" => $data["fieldChoices"],
        ];
    }

    /**
     * @param array{fields: array<int, string>, fieldChoices: array<string, string[]>} $data
     * @return array{fields: Collection<int, FormRow>, fieldChoices: array<string, string[]>}
     */
    public static function deserialize(SerializerInterface $serializer, array $data): array
    {
        return [
            "fields" => new ArrayCollection(array_map(fn($elm) => $serializer->deserialize($elm, FormRow::class, format: "json"), $data["fields"])),
            "fieldChoices" => $data["fieldChoices"],
        ];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "fields" => [],
            "fieldChoices" => [],
        ]);

        $resolver->setAllowedTypes("fields", [Collection::class, "array"]);
        $resolver->setAllowedTypes("fieldChoices", "array");
    }

    /**
     * @param FormBuilderInterface<array<string, mixed>> $builder
     * @param array<string, mixed> $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $innerForm = $builder->create("search", FormType::class);

        /** @var FormRow $field */
        foreach ($options["fields"] as $field) {
            if ($field->getType() === FormRowTypeEnum::EntityType) {
                $entityTypes = explode("|", $field->getConfiguration()["entityType"]);

                if (count($entityTypes) > 1) {
                    $innerForm->add($field->getFieldName() . "_substance", FancyChoiceType::class, [
                        "label" => $field->getLabel(),
                        "required" => false,
                        "choices" => $options["fieldChoices"][$field->getFieldName() . "_substance"],
                        "multiple" => true,
                        "empty_data" => [],
                    ]);

                    $innerForm->add($field->getFieldName() . "_lot", FancyChoiceType::class, [
                        "label" => $field->getLabel() . " (Lot)",
                        "required" => false,
                        "choices" => $options["fieldChoices"][$field->getFieldName() . "_lot"],
                        "multiple" => true,
                        "empty_data" => [],
                    ]);
                } else {
                    $innerForm->add($field->getFieldName(), FancyChoiceType::class, [
                        "label" => $field->getLabel(),
                        "required" => false,
                        "choices" => $options["fieldChoices"][$field->getFieldName()],
                        "multiple" => true,
                        "empty_data" => [],
                    ]);
                }
            } elseif ($field->getType() === FormRowTypeEnum::TextType || $field->getType() === FormRowTypeEnum::TextAreaType) {
                $innerForm->add($field->getFieldName(), TextType::class, [
                    "label" => $field->getLabel(),
                    "required" => false,
                ]);
            } elseif ($field->getType() === FormRowTypeEnum::FloatType) {
                $scienticNumberOptions = $this->formRowService->getFloatTypeConfig($field)[1];

                $innerForm->add($field->getFieldName(), NumberSearchType::class, [
                    "label" => $field->getLabel(),
                    "required" => false,
                    // Symfony UX does not support encoding
                    "scientific_number_types" => true,
                    "scientific_number_options" => [
                        ... $scienticNumberOptions,
                        // Overwrite design options to have a more uniform user experience
                        "nan_value" => "",
                        "+inf_value" => "Inf",
                        "-inf_value" => "-Inf",
                    ],
                ]);
            } elseif ($field->getType() === FormRowTypeEnum::IntegerType) {
                $innerForm->add($field->getFieldName(), NumberSearchType::class, [
                    "label" => $field->getLabel(),
                    "required" => false,
                ]);
            }
        }

        $builder->add($innerForm);
    }
}
