<?php
declare(strict_types=1);

namespace App\Tests\Units;

use App\Units\UnitAmount;
use App\Units\UnitMolarAmount;
use App\Units\UnitNotSupportedException;
use PHPUnit\Framework\TestCase;

class UnitMolarAmountTest extends TestCase
{
    public function testMol()
    {
        $quantity = UnitMolarAmount::create(1, "mol");

        $this->assertSame(1.0, $quantity->getValue());
        $this->assertSame(UnitMolarAmount::getInstance(), $quantity->getUnit());
    }

    public function testMolInterconversionToAmount()
    {
        $quantityMolarAmount = UnitMolarAmount::create(1, "mol");
        $quantityAmount = $quantityMolarAmount->getUnit()->interconvertTo($quantityMolarAmount->getValue(), UnitAmount::getInstance());

        $this->assertEqualsWithDelta(6.02214076e23, $quantityAmount->getValue(), 0.00001e23);
    }

    public function testMolInterconversionFromAmount()
    {
        $quantityAmount = UnitAmount::create(1);
        $quantityMolarAmount = UnitMolarAmount::getInstance()->interconvertFrom($quantityAmount);

        $this->assertEqualsWithDelta(1.6605391e-24, $quantityMolarAmount->getValue(), 0.000001e-24);
    }
}
