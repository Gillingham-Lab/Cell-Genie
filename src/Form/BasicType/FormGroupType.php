<?php
declare(strict_types=1);

namespace App\Form\BasicType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class FormGroupType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define("icon")
            ->allowedTypes("null", "string")
            ->default(null);

        $resolver->define("icon_stack")
            ->allowedTypes("null", "string")
            ->default(null);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars["icon"] = $options["icon"];
        $view->vars["icon_stack"] = $options["icon_stack"];
    }
}