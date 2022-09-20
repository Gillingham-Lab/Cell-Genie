<?php
declare(strict_types=1);

namespace App\Form\Substance;

use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Form\SaveableType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OligoType extends SaveableType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                $builder->create("general", FormType::class, [
                    "inherit_data" => true,
                    "label" => "General information"
                ])
                ->add("shortName", TextType::class, [
                    "label" => "Short name",
                    "help" => "Short name of the oligo, must be unique among all substances.",
                ])
                ->add("longName", TextType::class, [
                    "label" => "Name",
                    "help" => "A longer, more descriptive name.",
                ])
                ->add("comment", TextareaType::class, [
                    "label" => "Comment",
                    "help" => "A short comment of the purpose of this oligo, or any other information.",
                    "required" => false,
                ])
            )
            ->add(
                $builder->create("structure", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Structure",
                ])
                ->add("sequence", TextareaType::class, [
                    "label" => "Sequence",
                    "help" => "The DNA oligomer sequence (5' to 3'). Add modified bases using the square bracket notation (e.g., [Hexylamine]ATG[FAM])",
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
            )
        ;
        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => Oligo::class,
        ]);

        parent::configureOptions($resolver);
    }
}