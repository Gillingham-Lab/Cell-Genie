<?php
declare(strict_types=1);

namespace App\Form\CompositeType;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Form\BasicType\FancyChoiceType;
use App\Form\BasicType\FormGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @extends AbstractType<array{x: string, y: string}>
 */
class XYFieldType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define("design")
            ->required()
            ->allowedTypes(ExperimentalDesign::class);

        $resolver->define("x_label")
            ->default(null)
            ->allowedTypes("null", "string");

        $resolver->define("y_label")
            ->default(null)
            ->allowedTypes("null", "string");

        $resolver->define("x_help")
            ->default(null)
            ->allowedTypes("null", "string");

        $resolver->define("y_help")
            ->default(null)
            ->allowedTypes("null", "string");

        $resolver->setDefaults(["inherit_data" => true]);
    }

    public function getParent(): string
    {
        return FormGroupType::class;
    }

    /**
     * @return array{array<string, string>, array<string, string>}
     */
    public function getFieldChoices(?ExperimentalDesign $design): array
    {
        if ($design === null) {
            return [[], []];
        }

        $fieldChoices = [];
        $fieldGroups = [];
        foreach ($design->getFields() as $field) {
            $group = $field->getVariableRole()->value;
            $label = $field->getLabel() . " (" . $field->getFormRow()->getFieldName() . ")";
            $value = $field->getFormRow()->getFieldName();

            $fieldChoices[$label] = $value;
            $fieldGroups[$label] = $group;
        }

        return [$fieldChoices, $fieldGroups];
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        [$fieldChoices, $fieldGroups] = $this->getFieldChoices($options["design"]);

        $builder
            ->add("x", FancyChoiceType::class, [
                "label" => $options["x_label"] ?? "X Field",
                "help" => $options["x_help"],
                "required" => true,
                "choices" => $fieldChoices,
                "group_by" => fn($choice, $key, $value) => $fieldGroups[$key],
                "allow_empty" => true,
                "multiple" => false,
                "placeholder" => "Select a field for X",
                "empty_data" => null,
                "constraints" => [
                    new NotBlank(),
                ],
            ])
            ->add("y", FancyChoiceType::class, [
                "label" => $options["y_label"] ?? "Y Field",
                "help" => $options["y_help"],
                "required" => true,
                "choices" => $fieldChoices,
                "group_by" => fn($choice, $key, $value) => $fieldGroups[$key],
                "allow_empty" => true,
                "multiple" => false,
                "placeholder" => "Select a field for Y",
                "empty_data" => null,
                "constraints" => [
                    new NotBlank(),
                ],
            ])
        ;
    }
}