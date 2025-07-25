<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\File\File;
use App\Entity\DoctrineEntity\User\User;
use App\Form\BasicType\FancyEntityType;
use App\Form\BasicType\FormGroupType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @extends AbstractType<File>
 */
class VisualisationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("markedForRemoval", CheckboxType::class, options: [
                "empty_data" => null,
                "label" => "Remove image",
            ])
            ->add("title", TextType::class, options: [
                "label" => "Image title (not necessarily a file name)",
            ])
            ->add("description", TextareaType::class, options: [
                "label" => "Short image description.",
            ])
            ->add(
                $builder->create("_upload", FormGroupType::class, [
                    "label" => "Upload information",
                    "inherit_data" => true,
                    "icon" => "upload",
                    "icon_stack" => "view",
                ])
                ->add("uploadedBy", FancyEntityType::class, options: ["class" => User::class, "disabled" => true, "allow_empty" => true])
                ->add("uploadedOn", DateTimeType::class, options: ["disabled" => true, "html5" => false, "widget" => "single_text", "format" => "yyyy-MM-dd HH:mm:ss"]),
            )
            ->add(
                $builder->create("_metadata", FormGroupType::class, [
                    "label" => "File details",
                    "inherit_data" => true,
                    "icon" => "file",
                    "icon_stack" => "view",
                ])
                ->add("originalFileName", options: [
                    "disabled" => true,
                ])
                ->add("contentType", options: [
                    "label" => "Content type",
                    "disabled" => true,
                ])
                ->add("contentSize", options: [
                    "label" => "Size (in bytes)",
                    "disabled" => true,
                ]),
            )
            ->add("uploadedFile", FileType::class, options: [
                "label" => "Upload or replace file",
                "help" => "Maximum file size is 5 MiB",
                "mapped" => false,
                "required" => true,
                "constraints" => [
                    new Assert\Image(
                        maxSize: "5M",
                        mimeTypes: [
                            "image/png",
                            "image/jpg",
                            "image/jpeg",
                            "image/webp",
                        ],
                    ),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => File::class,
        ]);
    }
}
