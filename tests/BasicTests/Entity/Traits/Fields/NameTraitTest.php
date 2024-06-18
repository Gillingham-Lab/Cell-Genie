<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\Traits\Fields;

use App\Entity\Traits\Fields\NameTrait;
use App\Entity\Traits\Fields\ShortNameTrait;
use PHPUnit\Framework\TestCase;

class NameTraitTest extends TestCase
{
    public function testNameTraitHasAllShortNameTraitMethods()
    {
        $testClass = $this->getObjectForTrait(NameTrait::class);

        $traitMethods = get_class_methods(ShortNameTrait::class);

        foreach ($traitMethods as $traitMethod) {
            $this->assertTrue(method_exists($testClass, $traitMethod));
        }
    }

    public function testNameTraitHasAllLongNameTraitMethods()
    {
        $testClass = $this->getObjectForTrait(NameTrait::class);

        $traitMethods = get_class_methods(ShortNameTrait::class);

        foreach ($traitMethods as $traitMethod) {
            $this->assertTrue(method_exists($testClass, $traitMethod));
        }
    }
}
