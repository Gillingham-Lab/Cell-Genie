<?php
declare(strict_types=1);

namespace App\Tests\BasicTests\Genie\Units;

use App\Genie\Pole\Calculator;
use App\Genie\Pole\Exception\CalculationNotSupported;
use App\Genie\Pole\Unit\Amount;
use App\Genie\Pole\Unit\Mass;
use App\Genie\Pole\Unit\MassConcentration;
use App\Genie\Pole\Unit\MolarAmount;
use App\Genie\Pole\Unit\Volume;
use PHPUnit\Framework\TestCase;

class CalculatorTest extends TestCase
{
    public function testIfAmountAndAmountCanBeAdded(): void
    {
        $quantity1 = Amount::create(1);
        $quantity2 = Amount::create(1, "%");

        $calculator = new Calculator();

        $quantitySum = $calculator->add($quantity1, $quantity2);

        $this->assertSame(1.01, $quantitySum->getValue());
        $this->assertSame(Amount::getInstance(), $quantitySum->getUnit());
    }

    public function testIfMolarAmountAndAmountCanBeAdded(): void
    {
        $quantity1 = MolarAmount::create(1, "mol");
        $quantity2 = Amount::create(6.022e23);

        $calculator = new Calculator();

        $quantitySum = $calculator->add($quantity1, $quantity2);

        $this->assertEqualsWithDelta(2.0, $quantitySum->getValue(), 0.001);
        $this->assertSame(MolarAmount::getInstance(), $quantitySum->getUnit());
    }

    public function testIfAmountAndMolarAmountCanBeAdded(): void
    {
        $quantity1 = Amount::create(6.022e23);
        $quantity2 = MolarAmount::create(1, "mol");

        $calculator = new Calculator();

        $quantitySum = $calculator->add($quantity1, $quantity2);

        $this->assertEqualsWithDelta(12.044e23, $quantitySum->getValue(), 0.001e23);
        $this->assertSame(Amount::getInstance(), $quantitySum->getUnit());
    }

    public function testIfMolarAmountDividedByFloatGivesProperMolarAmount(): void
    {
        $quantity1 = MolarAmount::create(1, "nmol");
        $quantity2 = 4;

        $calculator = new Calculator();
        $result = $calculator->divide($quantity1, $quantity2);

        $this->assertSame(0.25, $result->getValueAs("nmol"));
        $this->assertSame(MolarAmount::getInstance(), $result->getUnit());
    }

    public function testIfMolarAmountDividedByAmountGivesProperMolarAmount(): void
    {
        $quantity1 = MolarAmount::create(1, "nmol");
        $quantity2 = Amount::create(4);

        $calculator = new Calculator();
        $result = $calculator->divide($quantity1, $quantity2);

        $this->assertSame(0.25, $result->getValueAs("nmol"));
        $this->assertSame(MolarAmount::getInstance(), $result->getUnit());
    }

    public function testIfMolarAmountMultipliedByFloatGivesProperMolarAmount(): void
    {
        $quantity1 = MolarAmount::create(1, "μmol");
        $quantity2 = 10;

        $calculator = new Calculator();
        $result = $calculator->multiply($quantity1, $quantity2);

        $this->assertSame(10.0, $result->getValueAs("μmol"));
        $this->assertSame(MolarAmount::getInstance(), $result->getUnit());
    }

    public function testIfMolarAmountMultipliedByAmountGivesProperMolarAmount(): void
    {
        $quantity1 = MolarAmount::create(1, "μmol");
        $quantity2 = Amount::create(10, "%");

        $calculator = new Calculator();
        $result = $calculator->multiply($quantity1, $quantity2);

        $this->assertSame(0.1, $result->getValueAs("μmol"));
        $this->assertSame(MolarAmount::getInstance(), $result->getUnit());
    }

    public function testIfMassDividedByVolumeGivesProperMassConcentration(): void
    {
        $quantity1 = Mass::create(1);
        $quantity2 = Volume::create(1);

        $calculator = new Calculator();

        $newQuantity = $calculator->divide($quantity1, $quantity2);

        $this->assertSame(1.0, $newQuantity->getValue());
        $this->assertSame(MassConcentration::getInstance(), $newQuantity->getUnit());
    }

    public function testIfVolumeDividedByMassThrowsProperException(): void
    {
        $quantity1 = Volume::create(1);
        $quantity2 = Mass::create(1);

        $calculator = new Calculator();

        $this->expectException(CalculationNotSupported::class);
        $newQuantity = $calculator->divide($quantity1, $quantity2);
    }

    public function testIfMassDividedByMassConcentrationGivesProperVolume(): void
    {
        $quantity1 = Mass::create(1, "mg");
        $quantity2 = MassConcentration::create(2, "mg/mL");

        $calculator = new Calculator();

        $newQuantity = $calculator->divide($quantity1, $quantity2);

        $this->assertSame(0.5, $newQuantity->getValueAs("mL"));
        $this->assertSame(Volume::getInstance(), $newQuantity->getUnit());
    }

    public function testIfVolumeMultipliedWithMassConcentrationGivesProperMass(): void
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