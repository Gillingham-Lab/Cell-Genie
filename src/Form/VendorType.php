<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\Vendor;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class VendorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("vendor", EntityType::class, options: [
                "label" => "Vendor",
                "class" => Vendor::class,
                "required" => false,
            ])
            ->add("vendorPN", TextType::class, options: [
                "label" => "Product number",
                "required" => false,
            ])
        ;
    }
}