<?php
declare(strict_types=1);

namespace App\Form\User;

use App\Entity\Param\ParamBag;
use App\Form\FancyChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class UserSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add("entityPageLimit", FancyChoiceType::class, [
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
                "required" => false,
            ])
            ->add("dateFormat", FancyChoiceType::class, [
                "label" => "Preferred format for datetime",
                "empty_data" => "d. m. Y",
                "choices" => $this->getDateFormats(),
                "help" => "Y = year, m = month, d = day",
                "allow_add" => true,
                "required" => false,
            ])
        ;
    }

    protected function getDateFormats(): array
    {
        $date = new \DateTime("now");

        $formats = [
            "Y-m-d",
            "d. m. Y",
            "m/d/Y",
            "d. M. Y",
            "d. F Y",
            "F d, Y",
            "D, d. M. Y",
        ];

        return array_combine(array_map(fn ($x) => $date->format($x), $formats), $formats);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "data_class" => null,
        ]);
    }
}