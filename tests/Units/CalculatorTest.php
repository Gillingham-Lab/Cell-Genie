<?php
declare(strict_types=1);

namespace App\Tests\Units;

use App\Units\Calculator;
use App\Units\UnitAmount;
use App\Units\UnitMolarAmount;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    public function testIfAmountAndAmountCanBeAdded()
    {
        $quantity1 = UnitAmount::create(1);
        $quantity2 = UnitAmount::create(1, "%");

        $calculator = new Calculator();

        $quantitySum = $calculator->add($quantity1, $quantity2);

        $this->assertSame(1.01, $quantitySum->getValue());
        $this->assertSame(UnitAmount::getInstance(), $quantitySum->getUnit());
    }

    public function testIfMolarAmountAndAmountCanBeAdded()
    {
        $quantity1 = UnitMolarAmount::create(1, "mol");
        $quantity2 = UnitAmount::create(6.022e23);

        $calculator = new Calculator();

        $quantitySum = $calculator->add($quantity1, $quantity2);

        $this->assertEqualsWithDelta(2.0, $quantitySum->getValue(), 0.001);
        $this->assertSame(UnitMolarAmount::getInstance(), $quantitySum->getUnit());
    }

    public function testIfAmountAndMolarAmountCanBeAdded()
    {
        $quantity1 = UnitAmount::create(6.022e23);
        $quantity2 = UnitMolarAmount::create(1, "mol");

        $calculator = new Calculator();

        $quantitySum = $calculator->add($quantity1, $quantity2);

        $this->assertEqualsWithDelta(12.044e23, $quantitySum->getValue(), 0.001e23);
        $this->assertSame(UnitAmount::getInstance(), $quantitySum->getUnit());
    }
}