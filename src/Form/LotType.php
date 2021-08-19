<?php

namespace App\Form;

use App\Entity\Lot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Security;

class LotType extends AbstractType
{
    public function __construct(
        private Security $security,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add("number", options: [
                "label" => "Internal label",
                "required" => true,
            ])
            ->add('lotNumber', options: [
                "label" => "Lot number",
                "help" => "Manufactures lot number (for publications)",
                "required" => true,
            ])
            ->add("boughtOn", DateType::class, options: [
                "label" => "Bought on",
                "data" => new \DateTime(),
                "required" => true,
            ])
            ->add("openedOn", DateType::class, options: [
                "label" => "Opened on",
                "data" => new \DateTime(),
                "required" => true,
            ])
            ->add("boughtBy", options: [
                "required" => true,
                "data" => $this->security->getUser(),
            ])
            ->add("amount")
            ->add("purity")
            ->add("aliquoteSize")
            ->add("numberOfAliquotes")
            ->add("comment")
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Lot::class,
        ]);
    }
}
