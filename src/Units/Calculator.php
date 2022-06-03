<?php
declare(strict_types=1);

namespace App\Units;

class Calculator
{
    public function add(Quantity $quantity1, Quantity $quantity2)
    {
        if ($quantity1->getUnit() !== $quantity2->getUnit()) {
            if ($quantity1->getUnit()->supportsInterconversionFrom($quantity2->getUnit())) {
                $quantity2 = $quantity1->getUnit()->interconvertFrom($quantity2);
            } elseif ($quantity2->getUnit()->supportsInterconversionTo($quantity1->getUnit())) {
                $quantity2 = $quantity2->getUnit()->interconvertTo($quantity2->getValue(), $quantity1->getUnit());
            } else {
                throw new \InvalidArgumentException("Unit\\Calculator::add: The units of the two quantities must be the same type.");
            }
        }

        return new Quantity($quantity1->getValue() + $quantity2->getValue(), $quantity1->getUnit());
    }
}