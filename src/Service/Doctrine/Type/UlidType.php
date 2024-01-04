<?php
declare(strict_types=1);

namespace App\Service\Doctrine\Type;

use Symfony\Bridge\Doctrine\Types\AbstractUidType;

class UlidType extends AbstractUidType
{
    public const NAME = 'ulid';

    public function getName(): string
    {
        return self::NAME;
    }

    protected function getUidClass(): string
    {
        return Ulid::class;
    }
}
