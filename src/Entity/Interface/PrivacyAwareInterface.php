<?php
declare(strict_types=1);

namespace App\Entity\Interface;

use App\Genie\Enums\PrivacyLevel;

interface PrivacyAwareInterface extends GroupAwareInterface, OwnerAwareInterface
{
    public function getPrivacyLevel(): PrivacyLevel;
}
