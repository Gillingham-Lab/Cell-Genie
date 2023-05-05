<?php
declare(strict_types=1);

namespace App\Entity\Interface;

use App\Entity\DoctrineEntity\User\UserGroup;

interface GroupAwareInterface
{
    public function getGroup(): ?UserGroup;
}
