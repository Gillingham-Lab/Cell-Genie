<?php
declare(strict_types=1);

namespace App\Form\Collection;

use App\Form\SequenceAnnotationType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Tienvx\UX\CollectionJs\Form\CollectionJsType;

class SequenceAnnotationCollectionType extends CollectionJsType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            "required" => false,
            "entry_type" => SequenceAnnotationType::class,
            "by_reference" => false,
            "allow_add" => true,
            "allow_delete" => true,
            "call_post_add_on_init" => true,
            "attr" => array(
                "class" => "collection",
            ),
        ]);
    }
}