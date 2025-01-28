<?php
declare(strict_types=1);

namespace App\Form\Traits;

use App\Form\BasicType\FancyChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @template TData
 */
trait VocabularyTrait
{
    /**
     * @return null|string[]
     */
    private function getVocabularyChoices(string $name): ?array
    {
        $vocabEntry = $this->vocabularyRepository->findOneBy(["name" => $name]);

        return $vocabEntry?->getVocabulary();
    }

    /**
     * @param array<string, mixed> $options
     * @return array{0: class-string, 1: array<string, mixed>}
     */
    private function getTextOrChoiceOptions(string $vocabularyName, array $options = []): array
    {
        $vocabEntries = $this->getVocabularyChoices($vocabularyName);

        $options = array_merge_recursive([
            "attr"  => [
                "allow_empty" => true,
            ],
        ], $options);

        if ($vocabEntries) {
            $type = FancyChoiceType::class;

            $options["choices"] = array_combine($vocabEntries, $vocabEntries);
        } else {
            $type = TextType::class;

            unset($options["multiple"]);
            unset($options["placeholder"]);
        }

        return [
            $type,
            $options,
        ];
    }

    /**
     * @param FormBuilderInterface<TData> $builder
     * @param array<string, mixed> $options
     */
    private function addTextOrChoiceType(FormBuilderInterface $builder, string $field, ?string $vocabularyName, array $options): void
    {
        $vocabularyName ??= $field;
        $vocab = $this->getVocabularyChoices($vocabularyName);

        $choiceOptions = [];

        if ($vocab) {
            $choiceOptions["choices"] = array_combine($vocab, $vocab);
            $choiceOptions["placeholder"] = "Choose an option";
        }

        $builder->add($field, $vocab ? ChoiceType::class : TextType::class, options: array_merge_recursive($options, $choiceOptions));
    }
}