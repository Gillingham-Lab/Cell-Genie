<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\AntibodyDilution;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AntibodyDilutionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('antibody', options: ["attr" => ["data-widget" => "select2"]])
            ->add('dilution')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AntibodyDilution::class,
        ]);
    }
}
