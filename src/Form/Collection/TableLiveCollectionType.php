<?php
declare(strict_types=1);

namespace App\Form\Collection;

use Symfony\Component\Form\AbstractType;
use Symfony\UX\LiveComponent\Form\Type\LiveCollectionType;

class TableLiveCollectionType extends AbstractType
{
    public function getParent(): string
    {
        return LiveCollectionType::class;
    }
}
