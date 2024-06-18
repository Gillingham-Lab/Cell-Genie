<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\Traits\Fields;

use App\Entity\Traits\Fields\LongNameTrait;
use PHPUnit\Framework\TestCase;

class LongNameTraitTest extends TestCase
{
    public function testGetLongNameWithoutAnySetNameReturnsNull()
    {
        $testClass = $this->getObjectForTrait(LongNameTrait::class);

        $this->assertNull($testClass->getLongName());
    }

    public function testSetLongNameWillHaveGetNameReturningSameValue()
    {
        $testClass = $this->getObjectForTrait(LongNameTrait::class);

        $this->assertSame($testClass, $testClass->setLongName("Long Name"));
        $this->assertSame("Long Name", $testClass->getLongName());
    }

    public function testSetLongNameToNullWillNotCauseException()
    {
        $testClass = $this->getObjectForTrait(LongNameTrait::class);

        $this->assertSame($testClass, $testClass->setLongName("Long Name"));
        $this->assertSame($testClass, $testClass->setLongName(null));
        $this->assertNull($testClass->getLongName());
    }
}
