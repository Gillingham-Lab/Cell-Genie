<?php
declare(strict_types=1);

namespace App\Entity\Interface;

use App\Entity\DoctrineEntity\User\User;

interface OwnerAwareInterface
{
    public function getOwner(): ?User;
}
