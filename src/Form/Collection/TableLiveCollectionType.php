<?php
declare(strict_types=1);

namespace App\Form\Collection;

use App\Twig\Components\Live\Experiment\ExperimentConditionRowType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

class TableLiveCollectionType extends AbstractType
{
    public function getParent(): string
    {
        return LiveCollectionType::class;
    }
}
