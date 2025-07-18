<?php
declare(strict_types=1);

namespace App\Controller\Admin\Traits;

use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

trait VocabularyTrait
{
    /**
     * @param string $name
     * @return null|string[]
     */
    private function getVocabularyChoices(string $name): ?array
    {
        $vocabEntry = $this->vocabularyRepository->findOneBy(["name" => $name]);

        return $vocabEntry?->getVocabulary();
    }

    private function textFieldOrChoices(string $field, ?string $databaseField = null): ChoiceField|TextField
    {
        $databaseField ??= $field;
        $vocab = $this->getVocabularyChoices($databaseField);

        if ($vocab) {
            $crudField = ChoiceField::new($field)
                ->setTranslatableChoices(array_combine($vocab, $vocab));
        } else {
            $crudField = TextField::new($field);
        }

        return $crudField;
    }
}
