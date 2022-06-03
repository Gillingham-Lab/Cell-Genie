<?php
declare(strict_types=1);

namespace App\Units;

class Calculator
{
    protected array $divisions = [
        UnitMass::class . "/" . UnitVolume::class => UnitMassConcentration::class,
        UnitMass::class . "/" . UnitMassConcentration::class => UnitVolume::class,
    ];

    protected array $multiplications = [
        UnitMassConcentration::class . "*" . UnitVolume::class => UnitMass::class,
        UnitVolume::class . "*" . UnitMassConcentration::class => UnitMass::class,
    ];

    protected function tryToMakeQuantitiesCompatible(Quantity $quantity1, Quantity $quantity2): Quantity
    {
        if ($quantity1->getUnit() !== $quantity2->getUnit()) {
            if ($quantity1->getUnit()->supportsInterconversionFrom($quantity2->getUnit())) {
                $quantity2 = $quantity1->getUnit()->interconvertFrom($quantity2);
            } elseif ($quantity2->getUnit()->supportsInterconversionTo($quantity1->getUnit())) {
                $quantity2 = $quantity2->getUnit()->interconvertTo($quantity2->getValue(), $quantity1->getUnit());
            } else {
                throw new UnitInterconversionNotSupportedException("Unit\\Calculator::add: The units of the two quantities must be the same type.");
            }
        }

        return $quantity2;
    }

    protected function tryToFindOperationUnitResult(string $operation, Quantity $quantity1, Quantity $quantity2): UnitInterface
    {
        $lookupArray = match($operation) {
            "*" => $this->multiplications,
            "/" => $this->divisions,
            default => throw new OperationNotSupported("The operation '{$operation}' is not supported by the calculator"),
        };

        $lookupKey = $quantity1->getUnit()::class . $operation . $quantity2->getUnit()::class;

        if (!isset($lookupArray[$lookupKey])) {
            throw new CalculationNotSupported("The calculator does not know how to calculate {$lookupKey}, supported are: " . implode(", ", array_keys($lookupArray)));
        } else {
            $newUnit = $lookupArray[$lookupKey];
        }

        return $newUnit::getInstance();
    }

    /**
     * Adds the value of quantity2 to the value of quantity1.
     *
     * Units must be the same or at least compatible. If units are not the same, the calculator tries to convert
     *  quantity2 into the unit of quantity1.
     * @param Quantity $quantity1
     * @param Quantity $quantity2
     * @return Quantity
     */
    public function add(Quantity $quantity1, Quantity $quantity2)
    {
        $quantity2 = $this->tryToMakeQuantitiesCompatible($quantity1, $quantity2);

        return new Quantity($quantity1->getValue() + $quantity2->getValue(), $quantity1->getUnit());
    }

    /**
     * Substracts the value of quantity2 from the value of quantity1.
     *
     * Units must be the same or at least compatible. If units are not the same, the calculator tries to convert
     *  quantity2 into the unit of quantity1.
     * @param Quantity $quantity1
     * @param Quantity $quantity2
     * @return Quantity
     */
    public function subtract(Quantity $quantity1, Quantity $quantity2)
    {
        $quantity2 = $this->tryToMakeQuantitiesCompatible($quantity1, $quantity2);

        return new Quantity($quantity1->getValue() - $quantity2->getValue(), $quantity1->getUnit());
    }

    /**
     * Devides a given Quantity by a float or another Quantity. Only possible if the operation was registered within
     *  the calculator.
     * @param Quantity $quantity1
     * @param Quantity|float $quantity2
     * @return Quantity
     * @throws OperationNotSupported
     */
    public function divide(Quantity $quantity1, Quantity|float $quantity2)
    {
        if ($quantity2 instanceof Quantity) {
            // $quantity2 is a Quantity and not just a float, we need to compare the units and then
            //  decide what to do.

            if ($quantity2->getUnit() == UnitAmount::getInstance()) {
                // If the second quantity is unitless, keep the unit of quantity 1
                $newUnit = $quantity1->getUnit();

                // The same is NOT true if quantity 1 is unitless, this would result in a reciprocal unit which should
                //  be defined first. Eg, 1/time would be frequency.
            } else {
                // If not, we need to find a proper conversion pathway.
                try {
                    // Look up if we have a operation result for the unit pair.
                    $newUnit = $this->tryToFindOperationUnitResult("/", $quantity1, $quantity2);
                } catch (OperationNotSupported $e) {
                    // If not, we can try to interconvert the units, as this always results in a unitless result (UnitAmount).
                    try {
                        $quantity2 = $this->tryToMakeQuantitiesCompatible($quantity1, $quantity2);
                        $newUnit = UnitAmount::getInstance();
                    } catch (UnitInterconversionNotSupportedException) {
                        // If this fails, we re-raise the OperationNotSupported
                        throw $e;
                    }
                }
            }

            // Calculate the new value
            $newValue = $quantity1->getValue() / $quantity2->getValue();

            // And return the new quantity.
            return new Quantity($newValue, $newUnit);
        } else {
            // If $quantity2 is a float, we can simplify the calculation significantly and do not need to choose
            //  a new unit.
            return new Quantity($quantity1->getValue() / $quantity2, $quantity1->getUnit());
        }
    }

    /**
     * Multiplies a given Quantity by a float or another Quantity. Only possible if the operation was registered within
     *  the calculator.
     * @param Quantity $quantity1
     * @param Quantity|float $quantity2
     * @return Quantity
     * @throws OperationNotSupported
     */
    public function multiply(Quantity $quantity1, Quantity|float $quantity2)
    {
        if ($quantity2 instanceof Quantity) {
            // $quantity2 is a Quantity and not just a float, we need to compare the units and then
            //  decide what to do.

            if ($quantity2->getUnit() == UnitAmount::getInstance()) {
                // If the second quantity is unitless, keep the unit of quantity 1
                $newUnit = $quantity1->getUnit();
            } elseif ($quantity1->getUnit() == UnitAmount::getInstance()) {
                // The same is true if quantity 1 is unitless, but we keep the unit of quantity 2
                $newUnit = $quantity2->getUnit();
            } else {
                // Look up if we have a operation result for the unit pair.
                $newUnit = $this->tryToFindOperationUnitResult("*", $quantity1, $quantity2);
            }

            // Calculate the new value
            $newValue = $quantity1->getValue() * $quantity2->getValue();

            // And return the new quantity.
            return new Quantity($newValue, $newUnit);
        } else {
            // If $quantity2 is a float, we can simplify the calculation significantly and do not need to choose
            //  a new unit.
            return new Quantity($quantity1->getValue() * $quantity2, $quantity1->getUnit());
        }
    }
}