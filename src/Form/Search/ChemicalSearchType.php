<?php
declare(strict_types=1);

namespace App\Form\Search;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class ChemicalSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("shortName", TextType::class, [
                "label" => "Short name",
                "required" => false,
            ])
            ->add("anyName", TextType::class, [
                "label" => "Name",
                "required" => false,
            ])
            ->add("casNumber", TextType::class, [
                "label" => "CAS number",
                "required" => false,
            ])
            ->add("hasAvailableLots", ChoiceType::class, [
                "label" => "Has available lots",
                "choices" => [
                    "Yes" => "true",
                    "No" => "false",
                ],
                "empty_data" => null,
                "required" => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['csrf_protection' => false]);
    }
}
