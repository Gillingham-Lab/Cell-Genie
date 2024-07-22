<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Genie\Units;

use App\Genie\Pole\Unit\Amount;
use PHPUnit\Framework\TestCase;

class UnitAmountTest extends TestCase
{
    public function testEmptyAmount()
    {
        $quantity = Amount::create(1);

        $this->assertSame(1.0, $quantity->getValue());
        $this->assertSame(Amount::getInstance(), $quantity->getUnit());
    }

    public function testPercentAmount()
    {
        $quantity = Amount::create(1, Amount::PERCENT);
        $this->assertEqualsWithDelta(0.01, $quantity->getValue(), 1e-18);
    }

    public function testPermilleAmount()
    {
        $quantity = Amount::create(1, Amount::PERMILLE);
        $this->assertEqualsWithDelta(0.001, $quantity->getValue(), 1e-18);
    }

    public function testPpmAmount()
    {
        $quantity = Amount::create(1, Amount::PARTSPERMILLION);
        $this->assertEqualsWithDelta(1e-6, $quantity->getValue(), 1e-18);
    }

    public function testPpbAmount()
    {
        $quantity = Amount::create(1, Amount::PARTSPERBILLION);
        $this->assertEqualsWithDelta(1e-9, $quantity->getValue(), 1e-18);
    }

    public function testPptAmount()
    {
        $quantity = Amount::create(1, Amount::PARTSPERTRILLION);
        $this->assertEqualsWithDelta(1e-12, $quantity->getValue(), 1e-18);
    }

    public function testUnitConversions()
    {
        $quantity = Amount::create(0.1);

        $this->assertEqualsWithDelta(10, $quantity->getValueAs(Amount::PERCENT), 1e-6);
        $this->assertEqualsWithDelta(100, $quantity->getValueAs(Amount::PERMILLE), 1e-6);
        $this->assertEqualsWithDelta(1e5, $quantity->getValueAs(Amount::PARTSPERMILLION), 1e-6);
        $this->assertEqualsWithDelta(1e8, $quantity->getValueAs(Amount::PARTSPERBILLION), 1e-6);
        $this->assertEqualsWithDelta(1e11, $quantity->getValueAs(Amount::PARTSPERTRILLION), 1e-6);
    }
}