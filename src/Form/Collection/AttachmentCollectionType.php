<?php
declare(strict_types=1);

namespace App\Form\Collection;

use App\Form\BasicType\FancyCollectionType;
use App\Form\DocumentationType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<DocumentationType[]>
 */
class AttachmentCollectionType extends AbstractType
{
    public function getParent(): string
    {
        return FancyCollectionType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            "required" => false,
            "entry_type" => DocumentationType::class,
            "by_reference" => false,
            "allow_add" => true,
            "allow_delete" => true,
            "attr" => array(
                "class" => "collection",
            ),
        ]);
    }
}