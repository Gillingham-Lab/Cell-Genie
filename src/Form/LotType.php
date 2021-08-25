<?php
declare(strict_types=1);

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
            ])
            ->add('lotNumber', options: [
                "label" => "Lot number",
                "help" => "Manufactures lot number (for publications)",
            ])
            ->add("boughtOn", options: [
                "label" => "Bought on",
            ])
            ->add("openedOn", options: [
                "label" => "Opened on",
            ])
            ->add("boughtBy", options: [
                "required" => true,
                "data" => $this->security->getUser(),
            ])
            ->add("box")
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
