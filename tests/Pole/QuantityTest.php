<?php
declare(strict_types=1);

namespace App\Tests\Pole;

use App\Genie\Pole\Quantity;
use App\Genie\Pole\Unit\Amount;
use App\Genie\Pole\Unit\MolarAmount;
use PHPUnit\Framework\TestCase;

class QuantityTest extends TestCase
{
    protected function getQuantity(float $value = 1.0, string $unit = "mol", string $class = MolarAmount::class)
    {
        $quantity = $class::create($value, $unit);
        return $quantity;
    }

    /*public function testGetValue()
    {

    }

    public function testGetValueAs()
    {

    }

    public function testIsUnit()
    {

    }

    public function testGetUnit()
    {

    }*/

    public function testSignificantDigits()
    {
        $quantity = $this->getQuantity();

        $this->assertSame("1.0000", $quantity->significantDigits(1.0, 5));
        $this->assertSame("1.000", $quantity->significantDigits(1.0, 4));
        $this->assertSame("1.00", $quantity->significantDigits(1.0, 3));
        $this->assertSame("1.0", $quantity->significantDigits(1.0, 2));
        $this->assertSame("1", $quantity->significantDigits(1.0, 1));

        $this->assertSame("112.30", $quantity->significantDigits(112.3, 5));
        $this->assertSame("112.3", $quantity->significantDigits(112.3, 4));
        $this->assertSame("112", $quantity->significantDigits(112.3, 3));
        $this->assertSame("110", $quantity->significantDigits(112.3, 2));
        $this->assertSame("100", $quantity->significantDigits(112.3, 1));

        $this->assertSame("11.2", $quantity->significantDigits(11.2, 3));
        $this->assertSame("11", $quantity->significantDigits(11.2, 2));
        $this->assertSame("10", $quantity->significantDigits(11.2, 1));
    }

    public function testFormatNormal()
    {
        $quantity = $this->getQuantity(12.3456);

        $this->assertSame("12.3", $quantity->format(3, Quantity::FORMAT_NORMAL));
    }

    public function testFormatScientific()
    {
        $quantity = $this->getQuantity(12.3456);
        $this->assertSame("1.235e1", $quantity->format(4, Quantity::FORMAT_SCIENTIFICALLY));
        $this->assertSame("1.23e1", $quantity->format(3, Quantity::FORMAT_SCIENTIFICALLY));
        $this->assertSame("1.2e1", $quantity->format(2, Quantity::FORMAT_SCIENTIFICALLY));

        $quantity = $this->getQuantity(0.0123456);
        $this->assertSame("1.235e-2", $quantity->format(4, Quantity::FORMAT_SCIENTIFICALLY));
        $this->assertSame("1.23e-2", $quantity->format(3, Quantity::FORMAT_SCIENTIFICALLY));
        $this->assertSame("1.2e-2", $quantity->format(2, Quantity::FORMAT_SCIENTIFICALLY));
    }

    public function testFormatEngineering()
    {
        $quantity = $this->getQuantity(12.3456);
        $this->assertSame("12.35", $quantity->format(4, Quantity::FORMAT_ENGINEERING));
        $this->assertSame("12.3", $quantity->format(3, Quantity::FORMAT_ENGINEERING));
        $this->assertSame("12", $quantity->format(2, Quantity::FORMAT_ENGINEERING));

        $quantity = $this->getQuantity(0.0123456);
        $this->assertSame("12.35e-3", $quantity->format(4, Quantity::FORMAT_ENGINEERING));
        $this->assertSame("12.3e-3", $quantity->format(3, Quantity::FORMAT_ENGINEERING));
        $this->assertSame("12e-3", $quantity->format(2, Quantity::FORMAT_ENGINEERING));

        $quantity = $this->getQuantity(12.3456e-6);
        $this->assertSame("12.35e-6", $quantity->format(4, Quantity::FORMAT_ENGINEERING));
        $this->assertSame("12.3e-6", $quantity->format(3, Quantity::FORMAT_ENGINEERING));
        $this->assertSame("12e-6", $quantity->format(2, Quantity::FORMAT_ENGINEERING));

        $quantity = $this->getQuantity(12345.6);
        $this->assertSame("12.35e3", $quantity->format(4, Quantity::FORMAT_ENGINEERING));
        $this->assertSame("12.3e3", $quantity->format(3, Quantity::FORMAT_ENGINEERING));
        $this->assertSame("12e3", $quantity->format(2, Quantity::FORMAT_ENGINEERING));
    }

    public function testFormatAdjustUnit()
    {
        $quantity = $this->getQuantity(12.3456, "mol");
        $this->assertSame("12.35 mol", $quantity->format(4, Quantity::FORMAT_ADJUST_UNIT));
        $this->assertSame("12.3 mol", $quantity->format(3, Quantity::FORMAT_ADJUST_UNIT));
        $this->assertSame("12 mol", $quantity->format(2, Quantity::FORMAT_ADJUST_UNIT));

        $quantity = $this->getQuantity(12.3456, "Gmol");
        $this->assertSame("12.35 Gmol", $quantity->format(4, Quantity::FORMAT_ADJUST_UNIT));
        $this->assertSame("12.3 Gmol", $quantity->format(3, Quantity::FORMAT_ADJUST_UNIT));
        $this->assertSame("12 Gmol", $quantity->format(2, Quantity::FORMAT_ADJUST_UNIT));

        $quantity = $this->getQuantity(12.3456e6, "Gmol");
        $this->assertSame("12350000 Gmol", $quantity->format(4, Quantity::FORMAT_ADJUST_UNIT));
        $this->assertSame("12300000 Gmol", $quantity->format(3, Quantity::FORMAT_ADJUST_UNIT));
        $this->assertSame("12000000 Gmol", $quantity->format(2, Quantity::FORMAT_ADJUST_UNIT));

        $quantity = $this->getQuantity(12.3456, "mmol");
        $this->assertSame("12.35 mmol", $quantity->format(4, Quantity::FORMAT_ADJUST_UNIT));
        $this->assertSame("12.3 mmol", $quantity->format(3, Quantity::FORMAT_ADJUST_UNIT));
        $this->assertSame("12 mmol", $quantity->format(2, Quantity::FORMAT_ADJUST_UNIT));

        $quantity = $this->getQuantity(12.3456, "amol");
        $this->assertSame("12.35 amol", $quantity->format(4, Quantity::FORMAT_ADJUST_UNIT));
        $this->assertSame("12.3 amol", $quantity->format(3, Quantity::FORMAT_ADJUST_UNIT));
        $this->assertSame("12 amol", $quantity->format(2, Quantity::FORMAT_ADJUST_UNIT));

        $quantity = $this->getQuantity(1, "%", Amount::class);
        $this->assertSame("10.00 ‰", $quantity->format(4, Quantity::FORMAT_ADJUST_UNIT));

        $quantity = $this->getQuantity(3085, "μmol");
        $this->assertSame("3.085 mmol", $quantity->format(4, Quantity::FORMAT_ADJUST_UNIT));
        $this->assertSame("3.09 mmol", $quantity->format(3, Quantity::FORMAT_ADJUST_UNIT));
        $this->assertSame("3.1 mmol", $quantity->format(2, Quantity::FORMAT_ADJUST_UNIT));
    }
}
