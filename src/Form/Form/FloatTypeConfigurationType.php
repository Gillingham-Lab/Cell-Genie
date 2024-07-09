<?php
declare(strict_types=1);

namespace App\Form\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class FloatTypeConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("datatype_float", ChoiceType::class, [
                "label" => "Float Size",
                "help" => "Choose the float size. All types support negative values.",
                "choices" => [
                    "32 Bit (single, ca 7 significant digits)" => 1,
                    "64 Bit (double, ca 15 significant digits)" => 2,
                ],
                "required" => true,
                "empty_data" => 1,
            ])
        ;
    }
}