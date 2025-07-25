<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\Traits\Fields;

use App\Entity\Traits\Fields\ShortNameTrait;
use PHPUnit\Framework\TestCase;

class ShortNameTraitTest extends TestCase
{
    public function testGetShortNameWithoutAnySetNameReturnsNull(): void
    {
        $testClass = $this->getObjectForTrait(ShortNameTrait::class);

        $this->assertNull($testClass->getShortName());
    }

    public function testSetShortNameWillHaveGetNameReturningSameValue(): void
    {
        $testClass = $this->getObjectForTrait(ShortNameTrait::class);

        $this->assertSame($testClass, $testClass->setShortName("Short Name"));
        $this->assertSame("Short Name", $testClass->getShortName());
    }

    public function testSetShortNameToNullWillNotCauseException(): void
    {
        $testClass = $this->getObjectForTrait(ShortNameTrait::class);

        $this->assertSame($testClass, $testClass->setShortName("Short Name"));
        $this->assertSame($testClass, $testClass->setShortName(null));
        $this->assertNull($testClass->getShortName());
    }

    public function testNotSettingShortNameReturnsStringContainingUnknown(): void
    {
        $testClass = $this->getObjectForTrait(ShortNameTrait::class);

        $this->assertSame("unknown", (string) $testClass);
    }

    public function testSettingShortNameCausesToStringConversionToReturnIt(): void
    {

        $testClass = $this->getObjectForTrait(ShortNameTrait::class);

        $this->assertSame($testClass, $testClass->setShortName("Short Name"));
        $this->assertSame("Short Name", (string) $testClass);
    }
}
