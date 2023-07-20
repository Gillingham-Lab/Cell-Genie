<?php
declare(strict_types=1);

namespace App\Form\Import;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\DoctrineEntity\User\UserGroup;
use App\Form\User\PrivacyAwareType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ImportOligoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
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