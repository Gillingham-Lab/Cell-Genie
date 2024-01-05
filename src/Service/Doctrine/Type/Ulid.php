<?php
declare(strict_types=1);

namespace App\Service\Doctrine\Type;

use Symfony\Component\Uid\Ulid as SymfonyUlid;

class Ulid extends SymfonyUlid
{
    public function __toString(): string
    {
        return $this->toRfc4122();
    }
}