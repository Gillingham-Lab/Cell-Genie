<?php
declare(strict_types=1);

namespace App\Form\Instrument;

use App\Form\BasicType\FancyCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<InstrumentUserType[]>
 */
class InstrumentUserCollectionType extends AbstractType
{
    public function getParent(): string
    {
        return FancyCollectionType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "required" => false,
            "entry_type" => InstrumentUserType::class,
            "by_reference" => false,
            "allow_add" => true,
            "allow_delete" => true,
            "allow_move_up" => true,
            "allow_move_down" => true,
            "attr" => [
                "class" => "collection",
            ],
        ]);
    }
}
