<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template TData
 * @extends AbstractType<TData>
 */
class FancyChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "allow_add" => false,
        ]);

        $resolver->setAllowedTypes("allow_add", "bool");
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options["required"] === false) {
            $view->vars["attr"]["data-allow-empty"] = true;
        }

        $view->vars["attr"]["class"] = "gin-fancy-select";
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}