<?php
declare(strict_types=1);

namespace App\Form\Instrument;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Tienvx\UX\CollectionJs\Form\CollectionJsType;

class InstrumentUserCollectionType extends CollectionJsType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            "required" => false,
            "entry_type" => InstrumentUserType::class,
            "by_reference" => false,
            "allow_add" => true,
            "allow_delete" => true,
            "allow_move_up" => true,
            "allow_move_down" => true,
            "call_post_add_on_init" => true,
            "attr" => array(
                "class" => "collection",
            ),
        ]);
    }
}