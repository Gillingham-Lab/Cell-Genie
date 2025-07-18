<?php
declare(strict_types=1);

namespace App\Form\BasicType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<mixed>
 */
class ExpressionType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->define("environment")
            ->allowedTypes("array")
            ->default([])
        ;
    }

    public function getParent(): string
    {
        return TextType::class;
    }
}
