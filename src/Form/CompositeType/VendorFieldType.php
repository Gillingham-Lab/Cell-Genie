<?php
declare(strict_types=1);

namespace App\Form\CompositeType;

use App\Entity\DoctrineEntity\Vendor;
use App\Form\BasicType\FancyEntityType;
use App\Form\BasicType\FormGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class VendorFieldType extends AbstractType
{
    public function getParent(): string
    {
        return FormGroupType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "icon" => "vendor",
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("vendor", FancyEntityType::class, options: [
                "label" => "Vendor",
                "class" => Vendor::class,
                "required" => false,
                'empty_data' => null,
                "placeholder" => "Empty",
                "allow_empty" => true,
            ])
            ->add("vendorPN", TextType::class, options: [
                "label" => "Product number",
                "required" => false,
            ])
        ;
    }
}
