<?php
declare(strict_types=1);

namespace App\Form\CompositeType;

use App\Form\BasicType\FormGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChemicalStructureType extends AbstractType
{
    public function getParent(): string
    {
        return FormGroupType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("smiles", TextType::class, [
                "label" => "SMILES",
                "help" => "The SMILES representation of the structure. Use a tool such as ChemDraw to paste the structure.",
                "required" => false,
                "empty_data" => "",
            ])
            ->add("molecularMass", NumberType::class, [
                "label" => "Molecular mass [Da]",
                "help" => "Molecular mass of the structure.",
                "scale" => 3,
                "required" => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault("inherit_data", true);
        $resolver->setDefault("label", "Storage location");
        $resolver->setDefault("icon", "chemical");
    }
}