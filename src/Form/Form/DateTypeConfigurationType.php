<?php
declare(strict_types=1);

namespace App\Form\Form;

use App\Form\BasicType\FormGroupType;
use Symfony\Component\Form\AbstractType;

/**
 * @template TData
 * @extends AbstractType<TData>
 */
class DateTypeConfigurationType extends AbstractType
{
    public function getParent(): string
    {
        return FormGroupType::class;
    }
}
