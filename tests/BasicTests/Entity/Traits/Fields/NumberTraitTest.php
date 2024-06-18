<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Entity\Traits\Fields;

use App\Entity\Traits\Fields\NumberTrait;
use PHPUnit\Framework\TestCase;

class NumberTraitTest extends TestCase
{
    public function testGetNumberWithoutAnySetNameReturnsNull()
    {
        $testClass = $this->getObjectForTrait(NumberTrait::class);

        $this->assertNull($testClass->getNumber());
    }

    public function testSetNumberWillHaveGetNameReturningSameValue()
    {
        $testClass = $this->getObjectForTrait(NumberTrait::class);

        $this->assertSame($testClass, $testClass->setNumber("Number"));
        $this->assertSame("Number", $testClass->getNumber());
    }

    public function testSetNumberToNullWillNotCauseException()
    {
        $testClass = $this->getObjectForTrait(NumberTrait::class);

        $this->assertSame($testClass, $testClass->setNumber("Number"));
        $this->assertSame($testClass, $testClass->setNumber(null));
        $this->assertNull($testClass->getNumber());
    }
}
