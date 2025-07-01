<?php
declare(strict_types=1);

namespace App\Form\Substance;

use App\Form\BasicType\FancyCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LotCollectionType extends AbstractType
{
    public function getParent(): string
    {
        return FancyCollectionType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "required" => false,
            "entry_type" => LotType::class,
            "by_reference" => false,
            "allow_add" => true,
            "allow_delete" => true,
            "allow_move_up" => true,
            "allow_move_down" => true,
            "attr" => array(
                "class" => "collection",
            ),
        ]);
    }
}