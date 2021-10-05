<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\ExperimentalCondition;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExperimentalConditionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("title", options: ["help" => "Short name of the condition"])
            ->add("description", options: ["help" => "Short description of the condition"])
            ->add('general', options: ["help" => "Turn on to make this condition global"])
            ->add("order", options: ["help" => "Order in which it the measurements should appear in forms"])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ExperimentalCondition::class,
        ]);
    }
}