<?php
declare(strict_types=1);

namespace App\Form\Experiment;

use App\Form\BasicType\ExpressionType;
use App\Validator\Constraint\ValidExpression;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<array<string, mixed>>
 */
class ExperimentalModelParamType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define("environment")
            ->allowedTypes("array")
            ->default([])
        ;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("initial", ExpressionType::class, [
                "required" => false,
                "empty_data" => null,
                "constraints" => [
                    new ValidExpression($options["environment"]),
                ],
                "environment" => $options["environment"],
            ])
            ->add("min", NumberType::class, [
                "required" => false,
                "empty_data" => null,
            ])
            ->add("max", NumberType::class, [
                "required" => false,
                "empty_data" => null,
            ])
            ->add("vary", CheckboxType::class, [
                "required" => false,
                "empty_data" => null,
            ])
        ;
    }
}
