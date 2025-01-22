<?php
declare(strict_types=1);

namespace App\Form\Form;

use App\Genie\Enums\FloatTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotNull;

/**
 * @template TData
 * @extends AbstractType<TData>
 */
class FloatTypeConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("datatype_float", ChoiceType::class, [
                "label" => "Float Size",
                "help" => "Choose the float size. All types support negative values.",
                "choices" => [
                    "32 Bit (single, ca 7 significant digits)" => FloatTypeEnum::Float32->value,
                    "64 Bit (double, ca 15 significant digits)" => FloatTypeEnum::Float64->value,
                ],
                "constraints" => [
                    new NotNull(),
                ],
                "required" => false,
                "empty_data" => FloatTypeEnum::Float32->value,
            ])
            ->add("floattype_inactive", ChoiceType::class, [
                "label" => "Inactive values are safed as",
                "choices" => [
                    "NaN" => "NaN",
                    "Inf" => "Inf",
                    "-Inf" => "-Inf",
                ],
                "empty_data" => "Inf",
                "help" => "NaN is 'not a number' and is typically used to represent missing or illegal values. Instead, Inf represents positive infinity, " .
                            "and -Inf represents negative infinity. If a value is expected to be larger than the sensitivity of the assay can detect, it " .
                            "makes sense to use +Inf to present inactive values (eg, when the IC50 is too large or was not measurable).",
                "required" => false,
            ])
            ->add("floattype_inactive_label", TextType::class, [
                "label" => "Inactive values are displayed as",
                "empty_data" => null,
                "help" => "Use this to decide what string is used to reflect inactive compounds. This will internally get converted into whatever special number " .
                        "you set above. If you leave this empty, inactive compounds are not specially marked.",
                "required" => false,
            ])
        ;
    }
}