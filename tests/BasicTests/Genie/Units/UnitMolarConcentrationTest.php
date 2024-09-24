<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Genie\Units;

use App\Genie\Pole\Unit\MolarConcentration;
use PHPUnit\Framework\TestCase;

class UnitMolarConcentrationTest extends TestCase
{
    public function testIfMolarGetsAccepted(): void
    {
        $quantity = MolarConcentration::create(1, "M");

        $this->assertSame(1.0, $quantity->getValue());
        $this->assertSame(1000.0, $quantity->getValueAs("mM"));
    }

    public function testIfMolePerLiterGetsAccepted(): void
    {
        $quantity = MolarConcentration::create(1, "mol/L");

        $this->assertSame(1.0, $quantity->getValue());
        $this->assertSame(1000.0, $quantity->getValueAs("mmol/L"));
        $this->assertSame(0.001, $quantity->getValueAs("mol/mL"));
    }
}