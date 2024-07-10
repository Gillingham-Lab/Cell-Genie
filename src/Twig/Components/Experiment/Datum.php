<?php
declare(strict_types=1);

namespace App\Twig\Components\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Genie\Enums\FormRowTypeEnum;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Datum
{
    public ?ExperimentalDesignField $field = null;
    public ?FormRow $formRow = null;
    public mixed $datum = null;

    public function isComponent(): bool
    {
        return match($this->formRow->getType()) {
            FormRowTypeEnum::EntityType => true,
            default => false,
        };
    }
}
