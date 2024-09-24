<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Genie\Units;

use App\Genie\Pole\Unit\Amount;
use App\Genie\Pole\Unit\MolarAmount;
use PHPUnit\Framework\TestCase;

class UnitMolarAmountTest extends TestCase
{
    public function testMol(): void
    {
        $quantity = MolarAmount::create(1, "mol");

        $this->assertSame(1.0, $quantity->getValue());
        $this->assertSame(MolarAmount::getInstance(), $quantity->getUnit());
    }

    public function testMolInterconversionToAmount(): void
    {
        $quantityMolarAmount = MolarAmount::create(1, "mol");
        $quantityAmount = $quantityMolarAmount->getUnit()->interconvertTo($quantityMolarAmount->getValue(), Amount::getInstance());

        $this->assertEqualsWithDelta(6.02214076e23, $quantityAmount->getValue(), 0.00001e23);
    }

    public function testMolInterconversionFromAmount(): void
    {
        $quantityAmount = Amount::create(1);
        $quantityMolarAmount = MolarAmount::getInstance()->interconvertFrom($quantityAmount);

        $this->assertEqualsWithDelta(1.6605391e-24, $quantityMolarAmount->getValue(), 0.000001e-24);
    }
}
