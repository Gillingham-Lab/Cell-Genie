<?php
declare(strict_types=1);

namespace App\Form\Import;

use App\Form\CompositeType\PrivacyAwareType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @template TData
 * @extends AbstractType<TData>
 */
class ImportOligoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->
            add("shortName", TextType::class, [

            ])
            ->add("longName", TextType::class, [

            ])
            ->add("comment", TextareaType::class, [
                "required" => false,
            ])
            ->add("_privacy", PrivacyAwareType::class, [
                "label" => "Privacy",
                "inherit_data" => true,
                "required" => false,
            ])
            ->add("sequence", TextareaType::class, [
                "required" => true,
            ])
            ->add("molecularMass", NumberType::class, [
                "label" => "Molecular mass [Da]",
                "required" => false,
            ])
            ->add("extinctionCoefficient", NumberType::class, [
                "label" => "Molar extinction coefficient ε [mM⁻¹ cm⁻¹]",
                "help" => "Extinction coefficient, as given by the manufacturer or as calculated. Must be in [mM⁻¹ cm⁻¹]",
                "required" => false,
            ])
        ;
    }
}