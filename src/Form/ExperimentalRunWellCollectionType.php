<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\Experiment;
use App\Entity\ExperimentalRunWellCollectionFormEntity;
use App\Entity\ExperimentalRunWellFormEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentalRunWellCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("wells", CollectionType::class, [
                "entry_type" => ExperimentalRunWellType::class,
                "entry_options" => [
                    "experiment" => $options["experiment"],
                    "label" => false,
                ]
            ])
            ->add("save", SubmitType::class, [
                "label" => "Save changes",
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            "data_class" => ExperimentalRunWellCollectionFormEntity::class,
        ]);

        $resolver->setRequired([
            "experiment",
        ]);

        $resolver->setAllowedTypes("experiment", Experiment::class);
    }
}