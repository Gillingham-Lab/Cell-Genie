<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

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
            ->add("uploadedBy", options: ["disabled" => true])
            ->add("uploadedOn", options: ["disabled" => true])
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
            ->add("uploadedFile", FileType::class, options: [
                "label" => "Upload or replace file",
                "help" => "Maximum file size is 20 MiB",
                "mapped" => false,
                "required" => true,
                "constraints" => [
                    new Assert\File(maxSize: "20480k")
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