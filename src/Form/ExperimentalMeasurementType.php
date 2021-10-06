<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\ExperimentalMeasurement;
use App\Entity\InputType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentalMeasurementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", options: ["help" => "Short name of the measurement"])
            ->add("description", type: TextareaType::class, options: ["help" => "A short description of the measurement"])
            ->add('internalStandard', options: ["help" => "Turn on to make this measurement an internal standard (all values of each well will be normalized against this value)"])
            ->add("order", options: ["help" => "Order in which it the measurements should appear in forms"])
            ->add("type", type: ChoiceType::class, options: [
                "help" => "Measurement type",
                "choices" => InputType::LABEL_TYPES,
            ])
            ->add("config", options: ["help" => "Type configuration. For choice fields, this is a comma-separated list of all possible choices."])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExperimentalMeasurement::class,
        ]);
    }
}