<?php
declare(strict_types=1);

namespace App\Form\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalModel;
use App\Form\CompositeType\PrivacyAwareType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

/**
 * @extends AbstractType<ExperimentalDesign>
 */
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
        $design = $builder->getData();

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
                    "entry_options" => [
                        "design" => $design,
                    ],
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
            ->add(
                $builder->create("_models", FormType::class, [
                    "inherit_data" => true,
                    "label" => "Models",
                ])
                ->add("models", LiveCollectionType::class, [
                    "entry_type" => ExperimentalModelType::class,
                    "by_reference" => false,
                    "entry_options" => [
                        "design" => $design,
                    ],
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