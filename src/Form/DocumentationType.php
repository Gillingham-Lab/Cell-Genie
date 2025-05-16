<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\File\File;
use App\Entity\DoctrineEntity\User\User;
use App\Form\BasicType\FancyEntityType;
use App\Form\BasicType\FormGroupType;
use Symfony\Component\Form\AbstractType;
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
class DocumentationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("title", TextType::class, options: [
                "label" => "File title (not necessarily a file name)",
            ])
            ->add("description", TextareaType::class, options: [
                "label" => "Short file description.",
            ])

            ->add(
                $builder->create("_upload", FormGroupType::class, [
                    "label" => "Upload information",
                    "inherit_data" => true,
                    "icon" => "upload",
                    "icon_stack" => "view",
                ])
                ->add("uploadedBy", FancyEntityType::class, options: ["class" => User::class, "disabled" => true, "allow_empty" => true])
                ->add("uploadedOn", DateTimeType::class, options: ["disabled" => true])
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
                ])
            )
            ->add("uploadedFile", FileType::class, options: [
                "label" => "Upload or replace file",
                "help" => "Maximum file size is 20 MiB",
                "mapped" => false,
                "required" => true,
                "constraints" => [
                    new Assert\File(maxSize: "20M")
                ]
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