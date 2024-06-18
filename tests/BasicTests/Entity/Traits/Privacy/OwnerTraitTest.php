<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\Traits\Privacy;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Traits\Privacy\OwnerTrait;
use PHPUnit\Framework\TestCase;

class OwnerTraitTest extends TestCase
{
    public function testGetOwnerReturnsNullIfNoneWasSet()
    {
        $testClass = $this->getObjectForTrait(OwnerTrait::class);

        $this->assertNull($testClass->getOwner());
    }

    public function testGetOwnerReturnsOwnerIfOwnerWasSetWithSetOwner()
    {
        $testClass = $this->getObjectForTrait(OwnerTrait::class);
        $user = $this->createMock(User::class);

        $testClass->setOwner($user);

        $this->assertSame($user, $testClass->getOwner($user));
    }
}
