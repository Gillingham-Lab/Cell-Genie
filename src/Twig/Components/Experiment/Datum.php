<?php
declare(strict_types=1);

namespace App\Twig\Components\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Genie\Enums\FormRowTypeEnum;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent]
class Datum
{
    public ?ExperimentalDesignField $field = null;
    public ?FormRow $formRow = null;
    public mixed $datum = null;

    #[PreMount]
    private function preMount($values)
    {
        if (!isset($values["formRow"]) && isset($values["field"])) {
            $values["formRow"] = $values["field"]->getFormRow();
        }

        return $values;
    }

    public function isComponent(): bool
    {
        return match($this->formRow->getType()) {
            FormRowTypeEnum::EntityType => true,
            default => false,
        };
    }
}
