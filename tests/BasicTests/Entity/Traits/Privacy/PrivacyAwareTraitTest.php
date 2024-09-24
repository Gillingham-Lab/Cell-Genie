<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\Traits\Privacy;

use App\Entity\Traits\Privacy\GroupOwnerTrait;
use App\Entity\Traits\Privacy\OwnerTrait;
use App\Entity\Traits\Privacy\PrivacyAwareTrait;
use App\Entity\Traits\Privacy\PrivacyLevelTrait;
use PHPUnit\Framework\TestCase;

class PrivacyAwareTraitTest extends TestCase
{
    public function testPrivacyAwareTraitWrapsOwnerTraits(): void
    {
        $testClass = $this->getObjectForTrait(PrivacyAwareTrait::class);

        $traitMethods = get_class_methods(OwnerTrait::class);

        foreach ($traitMethods as $traitMethod) {
            $this->assertTrue(method_exists($testClass, $traitMethod));
        }
    }

    public function testPrivacyAwareTraitWrapsGroupOwnerTraits(): void
    {
        $testClass = $this->getObjectForTrait(PrivacyAwareTrait::class);

        $traitMethods = get_class_methods(GroupOwnerTrait::class);

        foreach ($traitMethods as $traitMethod) {
            $this->assertTrue(method_exists($testClass, $traitMethod));
        }
    }

    public function testPrivacyAwareTraitWrapsPrivacyLevelTraits(): void
    {
        $testClass = $this->getObjectForTrait(PrivacyAwareTrait::class);

        $traitMethods = get_class_methods(PrivacyLevelTrait::class);

        foreach ($traitMethods as $traitMethod) {
            $this->assertTrue(method_exists($testClass, $traitMethod));
        }
    }
}
