<?php
declare(strict_types=1);

namespace App\Tests\Units;

use App\Units\CalculationNotSupported;
use App\Units\Calculator;
use App\Units\UnitAmount;
use App\Units\UnitMass;
use App\Units\UnitMassConcentration;
use App\Units\UnitMolarAmount;
use App\Units\UnitVolume;
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

    public function testIfMolarAmountDividedByFloatGivesProperMolarAmount()
    {
        $quantity1 = UnitMolarAmount::create(1, "nmol");
        $quantity2 = 4;

        $calculator = new Calculator();
        $result = $calculator->divide($quantity1, $quantity2);

        $this->assertSame(0.25, $result->getValueAs("nmol"));
        $this->assertSame(UnitMolarAmount::getInstance(), $result->getUnit());
    }

    public function testIfMolarAmountDividedByAmountGivesProperMolarAmount()
    {
        $quantity1 = UnitMolarAmount::create(1, "nmol");
        $quantity2 = UnitAmount::create(4);

        $calculator = new Calculator();
        $result = $calculator->divide($quantity1, $quantity2);

        $this->assertSame(0.25, $result->getValueAs("nmol"));
        $this->assertSame(UnitMolarAmount::getInstance(), $result->getUnit());
    }

    public function testIfMolarAmountMultipliedByFloatGivesProperMolarAmount()
    {
        $quantity1 = UnitMolarAmount::create(1, "μmol");
        $quantity2 = 10;

        $calculator = new Calculator();
        $result = $calculator->multiply($quantity1, $quantity2);

        $this->assertSame(10.0, $result->getValueAs("μmol"));
        $this->assertSame(UnitMolarAmount::getInstance(), $result->getUnit());
    }

    public function testIfMolarAmountMultipliedByAmountGivesProperMolarAmount()
    {
        $quantity1 = UnitMolarAmount::create(1, "μmol");
        $quantity2 = UnitAmount::create(10, "%");

        $calculator = new Calculator();
        $result = $calculator->multiply($quantity1, $quantity2);

        $this->assertSame(0.1, $result->getValueAs("μmol"));
        $this->assertSame(UnitMolarAmount::getInstance(), $result->getUnit());
    }

    public function testIfMassDividedByVolumeGivesProperMassConcentration()
    {
        $quantity1 = UnitMass::create(1);
        $quantity2 = UnitVolume::create(1);

        $calculator = new Calculator();

        $newQuantity = $calculator->divide($quantity1, $quantity2);

        $this->assertSame(1.0, $newQuantity->getValue());
        $this->assertSame(UnitMassConcentration::getInstance(), $newQuantity->getUnit());
    }

    public function testIfVolumeDividedByMassThrowsProperException()
    {
        $quantity1 = UnitVolume::create(1);
        $quantity2 = UnitMass::create(1);

        $calculator = new Calculator();

        $this->expectException(CalculationNotSupported::class);
        $newQuantity = $calculator->divide($quantity1, $quantity2);
    }

    public function testIfMassDividedByMassConcentrationGivesProperVolume()
    {
        $quantity1 = UnitMass::create(1, "mg");
        $quantity2 = UnitMassConcentration::create(2, "mg/mL");

        $calculator = new Calculator();

        $newQuantity = $calculator->divide($quantity1, $quantity2);

        $this->assertSame(0.5, $newQuantity->getValueAs("mL"));
        $this->assertSame(UnitVolume::getInstance(), $newQuantity->getUnit());
    }

    public function testIfVolumeMultipliedWithMassConcentrationGivesProperMass()
    {
        $quantity1 = UnitVolume::create(1, "μL");
        $quantity2 = UnitMassConcentration::create(100, "μg/mL");

        $calculator = new Calculator();
        $newQuantity = $calculator->multiply($quantity1, $quantity2);

        $this->assertSame(0.1, $newQuantity->getValueAs("μg"));
        $this->assertSame(UnitMass::getInstance(), $newQuantity->getUnit());

        // The other way around should work, too.
        $newQuantity = $calculator->multiply($quantity2, $quantity1);

        $this->assertSame(0.1, $newQuantity->getValueAs("μg"));
        $this->assertSame(UnitMass::getInstance(), $newQuantity->getUnit());
    }
}