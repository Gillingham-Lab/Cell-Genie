<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class ScientificNumberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addViewTransformer(new ScientificNumberTransformer(
            nan_values: $options["nan_values"],
            inf_values: $options["+inf_values"],
            ninf_values: $options["-inf_values"],
            nan_value: $options["nan_value"],
            inf_value: $options["+inf_value"],
            ninf_value: $options["-inf_value"],
        ), forcePrepend: true);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "nan_values" => ["NaN", "NA", "<NA>"],
            "nan_value" => "NA",
            "+inf_values" => ["Inf"],
            "+inf_value" => "Inf",
            "-inf_values" => ["-inf"],
            "-inf_value" => "-Inf",
        ]);

        $resolver->setAllowedTypes("nan_values", "string[]");
        $resolver->setAllowedTypes("+inf_values", "string[]");
        $resolver->setAllowedTypes("-inf_values", "string[]");

        $resolver->setAllowedTypes("nan_value", "string");
        $resolver->setAllowedTypes("+inf_value", "string");
        $resolver->setAllowedTypes("-inf_value", "string");
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
