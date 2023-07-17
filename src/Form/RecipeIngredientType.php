<?php

namespace App\Form;

use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\RecipeIngredient;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipeIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $units = ["M", "mM", "μM", "nM", "pM", "fM",
            "g/L", "mg/L", "μg/L",
            "g/mL", "mg/mL", "μg/mL",
            #"L/L", "mL/L", "μL/L", "mL/mL", "μL/mL", "%", "‰", "ppm", "ppb", "ppt",
        ];

        $builder
            ->add("chemical", type: EntityType::class, options: [
                "class" => Chemical::class,
                "choice_label" => "shortName",
            ])
            ->add("concentration", options: ["help" => "Short name of the condition"])
            ->add("concentration_unit", type: ChoiceType::class, options: [
                "help" => "Measurement type",
                "choices" => array_combine($units, $units),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RecipeIngredient::class,
        ]);
    }
}