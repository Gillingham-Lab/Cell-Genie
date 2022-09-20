<?php
declare(strict_types=1);

namespace App\Form\Traits;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

trait VocabularyTrait
{
    private function getVocabularyChoices(string $name): ?array
    {
        $vocabEntry = $this->vocabularyRepository->findOneBy(["name" => $name]);

        return $vocabEntry?->getVocabulary();
    }

    private function getTextOrChoiceOptions(string $vocabularyName, array $options = []): array
    {
        $vocabEntries = $this->getVocabularyChoices($vocabularyName);

        if ($vocabEntries) {
            $type = ChoiceType::class;

            $options["choices"] = array_combine($vocabEntries, $vocabEntries);
        } else {
            $type = TextType::class;
        }

        return [
            $type,
            $options,
        ];
    }

    private function addTextOrChoiceType(FormBuilderInterface $builder, string $field, ?string $vocabularyName, array $options)
    {
        $vocabularyName ??= $field;
        $vocab = $this->getVocabularyChoices($vocabularyName);

        $choiceOptions = [];

        if ($vocab) {
            $choiceOptions["choices"] = array_combine($vocab, $vocab);
            $choiceOptions["placeholder"] = "Choose an option";
        }

        $builder->add($field, $vocab ? ChoiceType::class : TextType::class, options: array_merge($options, $choiceOptions));
    }
}