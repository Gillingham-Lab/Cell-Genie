<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\Traits\Privacy;

use App\Entity\DoctrineEntity\User\User;
use App\Entity\Traits\Privacy\OwnerTrait;
use App\Entity\Traits\Privacy\PrivacyLevelTrait;
use App\Genie\Enums\PrivacyLevel;
use PHPUnit\Framework\TestCase;

class PrivacyLevelTraitTest extends TestCase
{
    public function testGetPrivacyLevelReturnsPublicIfNoneWasGiven()
    {
        $testClass = $this->getObjectForTrait(PrivacyLevelTrait::class);

        $this->assertSame(PrivacyLevel::Public, $testClass->getPrivacyLevel());
    }

    public function testGetPrivacyLevelReturnsPrivacyLevelThatWasSetWithSetPrivacyLevel()
    {
        $testClass = $this->getObjectForTrait(PrivacyLevelTrait::class);

        $testClass->setPrivacyLevel(PrivacyLevel::Group);
        $this->assertSame(PrivacyLevel::Group, $testClass->getPrivacyLevel());

        $testClass->setPrivacyLevel(PrivacyLevel::Private);
        $this->assertSame(PrivacyLevel::Private, $testClass->getPrivacyLevel());
    }
}
