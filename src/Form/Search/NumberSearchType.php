<?php
declare(strict_types=1);

namespace App\Form\Search;

use App\Form\ScientificNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class NumberSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $minOptions = [
            "label" => "Min",
            "attr" => [
                "placeholder" => "Min",
            ],
        ];
        $maxOptions = [
            "label" => "Max",
            "attr" => [
                "placeholder" => "Max",
            ],
        ];

        if ($options["scientific_number_types"]) {
            $builder
                ->add("min", ScientificNumberType::class, [
                    ...$minOptions,
                    ...$options["scientific_number_options"],
                ])
                ->add("max", ScientificNumberType::class, [
                    ...$maxOptions,
                    ...$options["scientific_number_options"],
                ])
            ;

            $builder->addViewTransformer(new NumberSearchTransformer());
        } else {
            $builder
                ->add("min", NumberType::class, $minOptions)
                ->add("max", NumberType::class, $maxOptions)
            ;
        }

        $builder->add("type", ChoiceType::class, [
            "label" => "Mode",
            "choices" => [
                "≥, ≤" => "11",
                "≥, <" => "10",
                ">, ≤" => "01",
                ">, <" => "00",
            ],
            "empty_data" => "11",
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "scientific_number_types" => false,
            "scientific_number_options" => [],
        ]);

        $resolver->setAllowedTypes("scientific_number_types", "bool");
    }
}
