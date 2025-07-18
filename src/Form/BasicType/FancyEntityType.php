<?php
declare(strict_types=1);

namespace App\Form\BasicType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @template TData
 * @extends AbstractType<TData>
 */
class FancyEntityType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define("allow_empty")
            ->allowedTypes("bool")
            ->default(false)
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options["required"] === false) {
            $view->vars["attr"]["data-allow-empty"] = "true";
        }

        $view->vars["attr"]["class"] = "gin-fancy-select-2";

        if ($options["allow_empty"]) {
            $view->vars["attr"]["data-allow-empty"] = "true";
        }
    }
    public function getParent(): string
    {
        return EntityType::class;
    }

    public function getBlockPrefix(): string
    {
        return "fancy_choice";
    }
}
