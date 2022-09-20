<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class NameType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("shortName", TextType::class, [
                "label" => "Short name",
                "help" => "Short name of the gene, must be unique among all substances.",
            ])
            ->add("longName", TextType::class, [
                "label" => "Name",
                "help" => "A longer, more descriptive name.",
            ])
        ;
    }
}