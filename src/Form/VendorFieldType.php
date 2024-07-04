<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\Vendor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class VendorFieldType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("vendor", EntityType::class, options: [
                "label" => "Vendor",
                "class" => Vendor::class,
                "required" => false,
                'empty_data' => null,
                "placeholder" => "Empty",
                "attr"  => [
                    "class" => "gin-fancy-select",
                    "data-allow-empty" => "true",
                ],
            ])
            ->add("vendorPN", TextType::class, options: [
                "label" => "Product number",
                "required" => false,
            ])
        ;
    }
}