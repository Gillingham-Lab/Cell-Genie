<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\ExperimentalCondition;
use App\Entity\InputType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentalConditionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("title", options: ["help" => "Short name of the condition"])
            ->add("description", options: ["help" => "Short description of the condition"])
            ->add('general', options: ["help" => "Turn on to make this condition global"])
            ->add("order", options: ["help" => "Order in which it the measurements should appear in forms"])
            ->add("type", type: ChoiceType::class, options: [
                "help" => "Measurement type",
                "choices" => InputType::LABEL_TYPES,
            ])
            ->add("config", options: ["help" => "Type configuration. For choice fields, this is a comma-separated list of all possible choices."])
            ->add("isX", options: [
                "help" => "Mark if this column is going to be used as X for the preview plot",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ExperimentalCondition::class,
        ]);
    }
}