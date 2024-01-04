<?php
declare(strict_types=1);

namespace App\Form\StockKeeping;

use App\Form\SaveableType;
use App\Genie\Enums\Availability;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;

class QuickOrderType extends SaveableType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("times", IntegerType::class, [
                "label" => "# times",
                "required" => true,
                "constraints" => [
                    new Assert\Range(min: 0),
                    new Assert\NotBlank(),
                ],
            ])
            ->add("numberOfUnits", IntegerType::class, [
                "label" => "Units per lot",
                "required" => true,
                "empty_data" => 1,
                "constraints" => [
                    new Assert\Range(min: 0)
                ],
            ])
            ->add("unitSize", IntegerType::class, [
                "label" => "Pcs per unit",
                "required" => true,
                "empty_data" => 1,
                "constraints" => [
                    new Assert\Range(min: 0)
                ],
            ])
            ->add("price", NumberType::class, [
                "label" => "Price",
                "required" => true,
                "empty_data" => null,
                "constraints" => [
                    new Assert\NotBlank(),
                    new Assert\Range(min: 0)
                ],
            ])
            ->add("status", EnumType::class, [
                "label" => "Status",
                "required" => true,
                "class" => Availability::class,
                "constraints" => [
                    new Assert\NotBlank(),
                ]
            ])
            ->add("location", TextType::class, [
                "label" => "Location",
                "required" => false,
            ])
        ;

        parent::buildForm($builder, $options);
    }
}