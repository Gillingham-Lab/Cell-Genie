<?php
declare(strict_types=1);

namespace App\Tests\Units;

use App\Pole\Calculator;
use App\Pole\Exception\CalculationNotSupported;
use App\Pole\Unit\Amount;
use App\Pole\Unit\Mass;
use App\Pole\Unit\MassConcentration;
use App\Pole\Unit\MolarAmount;
use App\Pole\Unit\Volume;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    public function testIfAmountAndAmountCanBeAdded()
    {
        $quantity1 = Amount::create(1);
        $quantity2 = Amount::create(1, "%");

        $calculator = new Calculator();

        $quantitySum = $calculator->add($quantity1, $quantity2);

        $this->assertSame(1.01, $quantitySum->getValue());
        $this->assertSame(Amount::getInstance(), $quantitySum->getUnit());
    }

    public function testIfMolarAmountAndAmountCanBeAdded()
    {
        $quantity1 = MolarAmount::create(1, "mol");
        $quantity2 = Amount::create(6.022e23);

        $calculator = new Calculator();

        $quantitySum = $calculator->add($quantity1, $quantity2);

        $this->assertEqualsWithDelta(2.0, $quantitySum->getValue(), 0.001);
        $this->assertSame(MolarAmount::getInstance(), $quantitySum->getUnit());
    }

    public function testIfAmountAndMolarAmountCanBeAdded()
    {
        $quantity1 = Amount::create(6.022e23);
        $quantity2 = MolarAmount::create(1, "mol");

        $calculator = new Calculator();

        $quantitySum = $calculator->add($quantity1, $quantity2);

        $this->assertEqualsWithDelta(12.044e23, $quantitySum->getValue(), 0.001e23);
        $this->assertSame(Amount::getInstance(), $quantitySum->getUnit());
    }

    public function testIfMolarAmountDividedByFloatGivesProperMolarAmount()
    {
        $quantity1 = MolarAmount::create(1, "nmol");
        $quantity2 = 4;

        $calculator = new Calculator();
        $result = $calculator->divide($quantity1, $quantity2);

        $this->assertSame(0.25, $result->getValueAs("nmol"));
        $this->assertSame(MolarAmount::getInstance(), $result->getUnit());
    }

    public function testIfMolarAmountDividedByAmountGivesProperMolarAmount()
    {
        $quantity1 = MolarAmount::create(1, "nmol");
        $quantity2 = Amount::create(4);

        $calculator = new Calculator();
        $result = $calculator->divide($quantity1, $quantity2);

        $this->assertSame(0.25, $result->getValueAs("nmol"));
        $this->assertSame(MolarAmount::getInstance(), $result->getUnit());
    }

    public function testIfMolarAmountMultipliedByFloatGivesProperMolarAmount()
    {
        $quantity1 = MolarAmount::create(1, "μmol");
        $quantity2 = 10;

        $calculator = new Calculator();
        $result = $calculator->multiply($quantity1, $quantity2);

        $this->assertSame(10.0, $result->getValueAs("μmol"));
        $this->assertSame(MolarAmount::getInstance(), $result->getUnit());
    }

    public function testIfMolarAmountMultipliedByAmountGivesProperMolarAmount()
    {
        $quantity1 = MolarAmount::create(1, "μmol");
        $quantity2 = Amount::create(10, "%");

        $calculator = new Calculator();
        $result = $calculator->multiply($quantity1, $quantity2);

        $this->assertSame(0.1, $result->getValueAs("μmol"));
        $this->assertSame(MolarAmount::getInstance(), $result->getUnit());
    }

    public function testIfMassDividedByVolumeGivesProperMassConcentration()
    {
        $quantity1 = Mass::create(1);
        $quantity2 = Volume::create(1);

        $calculator = new Calculator();

        $newQuantity = $calculator->divide($quantity1, $quantity2);

        $this->assertSame(1.0, $newQuantity->getValue());
        $this->assertSame(MassConcentration::getInstance(), $newQuantity->getUnit());
    }

    public function testIfVolumeDividedByMassThrowsProperException()
    {
        $quantity1 = Volume::create(1);
        $quantity2 = Mass::create(1);

        $calculator = new Calculator();

        $this->expectException(CalculationNotSupported::class);
        $newQuantity = $calculator->divide($quantity1, $quantity2);
    }

    public function testIfMassDividedByMassConcentrationGivesProperVolume()
    {
        $quantity1 = Mass::create(1, "mg");
        $quantity2 = MassConcentration::create(2, "mg/mL");

        $calculator = new Calculator();

        $newQuantity = $calculator->divide($quantity1, $quantity2);

        $this->assertSame(0.5, $newQuantity->getValueAs("mL"));
        $this->assertSame(Volume::getInstance(), $newQuantity->getUnit());
    }

    public function testIfVolumeMultipliedWithMassConcentrationGivesProperMass()
    {
        $quantity1 = Volume::create(1, "μL");
        $quantity2 = MassConcentration::create(100, "μg/mL");

        $calculator = new Calculator();
        $newQuantity = $calculator->multiply($quantity1, $quantity2);

        $this->assertSame(0.1, $newQuantity->getValueAs("μg"));
        $this->assertSame(Mass::getInstance(), $newQuantity->getUnit());

        // The other way around should work, too.
        $newQuantity = $calculator->multiply($quantity2, $quantity1);

        $this->assertSame(0.1, $newQuantity->getValueAs("μg"));
        $this->assertSame(Mass::getInstance(), $newQuantity->getUnit());
    }
}