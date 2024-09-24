<?php
declare(strict_types=1);

namespace App\Twig\Components\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesignField;
use App\Entity\DoctrineEntity\Form\FormRow;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\SubstanceLot;
use App\Genie\Enums\FormRowTypeEnum;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent]
class Datum
{
    public ?ExperimentalDesignField $field = null;
    public ?FormRow $formRow = null;
    public mixed $datum = null;
    public bool $small = false;

    #[PreMount]
    public function preMount($values)
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

    public function getChemical(): false|Chemical
    {
        if ($this->isComponent()) {
            if ($this->datum instanceof Chemical) {
                return $this->datum;
            } elseif ($this->datum instanceof SubstanceLot and $this->datum->getSubstance() instanceof Chemical) {
                return $this->datum->getSubstance();
            }
        }

        return false;
    }
}
