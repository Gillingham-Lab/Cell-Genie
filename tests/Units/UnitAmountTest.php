<?php
declare(strict_types=1);

namespace App\Tests\Units;

use App\Units\Quantity;
use App\Units\UnitAmount;
use PHPUnit\Framework\TestCase;

class UnitAmountTest extends TestCase
{
    public function testEmptyAmount()
    {
        $quantity = UnitAmount::create(1);

        $this->assertSame(1.0, $quantity->getValue());
        $this->assertSame(UnitAmount::getInstance(), $quantity->getUnit());
    }

    public function testPercentAmount()
    {
        $quantity = UnitAmount::create(1, UnitAmount::PERCENT);
        $this->assertEqualsWithDelta(0.01, $quantity->getValue(), 1e-18);
    }

    public function testPermilleAmount()
    {
        $quantity = UnitAmount::create(1, UnitAmount::PERMILLE);
        $this->assertEqualsWithDelta(0.001, $quantity->getValue(), 1e-18);
    }

    public function testPpmAmount()
    {
        $quantity = UnitAmount::create(1, UnitAmount::PARTSPERMILLION);
        $this->assertEqualsWithDelta(1e-6, $quantity->getValue(), 1e-18);
    }

    public function testPpbAmount()
    {
        $quantity = UnitAmount::create(1, UnitAmount::PARTSPERBILLION);
        $this->assertEqualsWithDelta(1e-9, $quantity->getValue(), 1e-18);
    }

    public function testPptAmount()
    {
        $quantity = UnitAmount::create(1, UnitAmount::PARTSPERTRILLION);
        $this->assertEqualsWithDelta(1e-12, $quantity->getValue(), 1e-18);
    }

    public function testUnitConversions()
    {
        $quantity = UnitAmount::create(0.1);

        $this->assertEqualsWithDelta(10, $quantity->getValueAs(UnitAmount::PERCENT), 1e-6);
        $this->assertEqualsWithDelta(100, $quantity->getValueAs(UnitAmount::PERMILLE), 1e-6);
        $this->assertEqualsWithDelta(1e5, $quantity->getValueAs(UnitAmount::PARTSPERMILLION), 1e-6);
        $this->assertEqualsWithDelta(1e8, $quantity->getValueAs(UnitAmount::PARTSPERBILLION), 1e-6);
        $this->assertEqualsWithDelta(1e11, $quantity->getValueAs(UnitAmount::PARTSPERTRILLION), 1e-6);
    }
}