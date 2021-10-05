<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\ExperimentalMeasurement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentalMeasurementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", options: ["help" => "Short name of the measurement"])
            ->add("description", options: ["help" => "A short description of the measurement"])
            ->add("order", options: ["help" => "Order in which it the measurements should appear in forms"])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExperimentalMeasurement::class,
        ]);
    }
}