<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\StockManagement\Consumable;
use App\Entity\DoctrineEntity\Vendor;
use App\Form\User\PrivacyAwareType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VendorType extends SaveableType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            $builder->create("_general", FormType::class, options: [
                "inherit_data" => true,
                "label" => "Info",
            ])
            ->add("name", TextType::class, [
                "required" => true,
            ])
            ->add("homepage", UrlType::class, [
                "required" => true,
            ])
            ->add("catalogUrl", TextType::class, [
                "help" => <<<TXT
                Use {pn} to annotate where the product number should be inserted. If not given, it will 
                always be attached to the end. If there is no easy catalog access via product number, 
                add # to the end of the url.    
                TXT,
                "required" => false,
            ])
            ->add("comment", CKEditorType::class, [
                "label" => "Comment",
                "sanitize_html" => true,
                "required" => false,
                "empty_data" => null,
                "config" => ["toolbar" => "basic"],
            ])
            ->add("hasFreeShipping", CheckboxType::class, [
                "required" => false,
                "empty_data" => null,
            ])
            ->add("hasDiscount", CheckboxType::class, [
                "required" => false,
                "empty_data" => null,
            ])
            ->add("isPreferred", CheckboxType::class, [
                "required" => false,
                "empty_data" => null,
            ])
            ->add("_privacy", PrivacyAwareType::class, [
                "inherit_data" => true,
                "label" => "Ownership",
            ])
        );

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Vendor::class,
        ]);

        parent::configureOptions($resolver);
    }
}