<?php
declare(strict_types=1);

namespace App\Entity\Traits\Privacy;

trait PrivacyAwareTrait
{
    use OwnerTrait;
    use GroupOwnerTrait;
    use PrivacyLevelTrait;
}