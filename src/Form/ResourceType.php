<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\Resource;
use App\Form\Collection\AttachmentCollectionType;
use App\Form\User\PrivacyAwareType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends SaveableType<Resource>
 */
class ResourceType extends SaveableType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create("_general", FormType::class, options: [
                    "inherit_data" => true,
                    "label" => "Info",
                ])
                ->add("longName", TextType::class, [
                    "required" => true,
                ])
                ->add("category", TextType::class, [
                    "required" => true,
                    "autocomplete" => true,
                    'tom_select_options' => [
                        'create' => true,
                        'createOnBlur' => true,
                        "maxItems" => 1,
                    ],
                    "autocomplete_url" => $options["category_autocomplete_url"],
                ])
                ->add("url", UrlType::class, [
                    "required" => true,
                ])
                ->add("comment", CKEditorType::class, [
                    "label" => "Comment",
                    "sanitize_html" => true,
                    "required" => false,
                    "empty_data" => null,
                    "config" => ["toolbar" => "basic"],
                ])
                ->add("_privacy", PrivacyAwareType::class, [
                    "inherit_data" => true,
                    "label" => "Ownership",
                ])
            )
            ->add(
                $builder->create("_visualisation", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Picture",
                ])
                ->add("visualisation", VisualisationType::class, [
                    #"inherit_data" => true,
                    "label" => "Visualisation",
                    "required" => false,
                ])
            )
            ->add(
                $builder->create("_attachments", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Attachments",
                ])
                ->add("attachments", AttachmentCollectionType::class, [
                    "label" => "Attachments",
                ])
            )
        ;

        parent::buildForm($builder, $options);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => Resource::class,
            "category_autocomplete_url" => "#",
        ]);

        $resolver->addAllowedTypes("category_autocomplete_url", "string");

        parent::configureOptions($resolver);
    }
}