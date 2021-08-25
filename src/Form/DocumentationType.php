<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\File;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataMapperInterface;
use Symfony\Component\Form\Exception;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class DocumentationType extends AbstractType
{
    public function __construct(
        private Security $security
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", options: [
                "label" => "File title (not necessarily a file name)",
            ])
            ->add("description", options: [
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
            ->add("uploadedFile", \Symfony\Component\Form\Extension\Core\Type\FileType::class, options: [
                "label" => "Upload or replace file",
                "help" => "Maximum file size is 20 MiB",
                "mapped" => false,
                "required" => true,
                "constraints" => [
                    new \Symfony\Component\Validator\Constraints\File(maxSize: "20480k")
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => File::class,
        ]);
    }
}