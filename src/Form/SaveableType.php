<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template TData
 * @extends AbstractType<TData>
 * @implements FormTypeInterface<TData>
 */
class SaveableType extends AbstractType implements FormTypeInterface
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
            "attr" => [
                "novalidate" => "novalidate",
            ],
        ]);

        $resolver->setAllowedTypes("save_button", "bool");
        $resolver->setAllowedTypes("save_label", "string");
    }
}
