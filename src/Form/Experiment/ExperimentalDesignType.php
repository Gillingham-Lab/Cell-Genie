<?php
declare(strict_types=1);

namespace App\Form\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Form\User\PrivacyAwareType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

class ExperimentalDesignType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => ExperimentalDesign::class,
        ]);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                $builder->create("_general", FormType::class, [
                    "inherit_data" => true,
                    "label" => "General",
                ])
                ->add("number", TextType::class, [
                    "label" => "Number",
                    "required"  => true,
                ])
                ->add("shortName", TextType::class, [
                    "label" => "Short Name",
                    "required"  => true,
                ])
                ->add("longName", TextType::class, [
                    "label" => "Long Name",
                    "required"  => true,
                ])
                ->add("ownership", PrivacyAwareType::class, [
                    "label" => "Ownership",
                    "required"  => true,
                    "inherit_data" => true,
                ])
            )
            ->add(
                $builder->create("_fields", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Fields",
                ])
                ->add("fields", LiveCollectionType::class, [
                    "entry_type" => ExperimentalDesignFieldType::class,
                    "by_reference" => false,
                    "button_delete_options" => [
                        "attr" => [
                            "class" => "btn btn-outline-danger",
                        ],
                    ],
                    "button_add_options" => [
                        "attr" => [
                            "class" => "btn btn-outline-primary",
                        ],
                    ],
                ])
            )
        ;
    }
}