<?php
declare(strict_types=1);

namespace App\Form;

use App\Entity\DoctrineEntity\Cell\CellCultureSplittingEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SaveableType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if ($options["save_button"]) {
            $builder->add("save", SubmitType::class);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "save_button" => false,
            "save_label" => "Save",
        ]);

        $resolver->setAllowedTypes("save_button", "bool");
        $resolver->setAllowedTypes("save_label", "string");
    }
}