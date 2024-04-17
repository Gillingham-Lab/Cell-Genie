<?php
declare(strict_types=1);

namespace App\Form\User;

use App\Entity\Param\ParamBag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("entityPageLimit", ChoiceType::class, [
                "label" => "Default page size for entities",
                "empty_data" => 30,
                "choices" => [
                    10 => 10,
                    20 => 20,
                    30 => 30,
                    50 => 50,
                    75 => 75,
                    100 => 100,
                ],
                "constraints" => [
                    new NotBlank(),
                ],
                "required" => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => null,
        ]);
    }
}