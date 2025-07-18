<?php
declare(strict_types=1);

namespace App\Form\BasicType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractType<list<mixed>>
 */
class FancyCollectionType extends AbstractType
{
    public function getParent(): string
    {
        return CollectionType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars = array_replace($view->vars, [
            'label_button_add' => $options['label_button_add'],
            'label_button_remove' => $options['label_button_remove'],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $entryOptionsNormalizer = function (Options $options, $value) {
            $value['block_prefix'] = $value['block_prefix'] ?? 'fancy_collection_entry';

            return $value;
        };

        $resolver->setDefaults([
            "label_button_add" => "Add",
            "label_button_remove" => "Remove",
            "allow_move_down" => false,
            "allow_move_up" => false,
        ]);

        $resolver->setAllowedTypes("label_button_add", "string");
        $resolver->setAllowedTypes("label_button_remove", "string");
        $resolver->setNormalizer('entry_options', $entryOptionsNormalizer);
        $resolver->setNormalizer('prototype_options', $entryOptionsNormalizer);
    }

    public function getBlockPrefix(): string
    {
        return 'fancy_collection';
    }
}
