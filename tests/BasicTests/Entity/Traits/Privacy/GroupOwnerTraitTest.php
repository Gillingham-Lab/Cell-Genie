<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\Traits\Privacy;

use App\Entity\DoctrineEntity\User\UserGroup;
use App\Entity\Traits\Privacy\GroupOwnerTrait;
use PHPUnit\Framework\TestCase;

class GroupOwnerTraitTest extends TestCase
{
    public function testGetGroupReturnsNullIfNoneWasSet(): void
    {
        $testClass = $this->getObjectForTrait(GroupOwnerTrait::class);

        $this->assertNull($testClass->getGroup());
    }

    public function testGetGroupReturnsGroupIfGroupWasSetWithSetGroup(): void
    {
        $testClass = $this->getObjectForTrait(GroupOwnerTrait::class);
        $user = $this->createMock(UserGroup::class);

        $testClass->setGroup($user);

        $this->assertSame($user, $testClass->getGroup($user));
    }
}
