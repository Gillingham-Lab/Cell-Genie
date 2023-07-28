<?php
declare(strict_types=1);

namespace App\Entity\Traits\Fields;

use App\Entity\Traits\ShortNameTrait;

trait NameTrait
{
    use ShortNameTrait;
    use LongNameTrait;
}