<?php
declare(strict_types=1);

namespace App\Genie\Pole;

abstract class CompositeUnit extends BaseUnit
{
    /** @var array<int, BaseUnit>  */
    protected array $factorial_units = [];
    /** @var array<int, BaseUnit>  */
    protected array $reciprocal_units = [];

    /**
     * Decomposes a given unit string into factorial and reciprocal units.
     *
     * Returns two arrays, the first with a list of factorial units, the second with a list of reciprocal units.
     * @param string $unitString
     * @return array<int, array<int, string>>
     */
    public function decompose_composite_unit_string(string $unitString): array
    {
        // Lets try if its a composite unit
        // Only supports easy syntax:
        //  - factorial units divided by *,
        //  - then reciprocal units divided by /
        $factorial_units = [];
        $reciprocal_units = [];
        $unitStringArray = str_split($unitString);

        $current_unit = "";
        $factorial = true;

        foreach ($unitStringArray as $c) {
            if ($c == "*") {
                if ($factorial === true) {
                    $factorial_units[] = $current_unit;
                    $current_unit = "";
                } else {
                    $reciprocal_units[] = $current_unit;
                    $current_unit = "";
                }

                $factorial = true;
            } elseif ($c == "/") {
                if ($factorial === true) {
                    $factorial_units[] = $current_unit;
                    $current_unit = "";
                } else {
                    $reciprocal_units[] = $current_unit;
                    $current_unit = "";
                }

                $factorial = false;
            } else {
                $current_unit .= $c;
            }
        }

        if ($factorial === true) {
            $factorial_units[] = $current_unit;
            unset($current_unit);
        } else {
            $reciprocal_units[] = $current_unit;
            unset($current_unit);
        }

        return [$factorial_units, $reciprocal_units];
    }

    public function supports(string $unitString): bool
    {
        $supports = parent::supports($unitString);

        if ($supports) {
            return true;
        }

        // Decompose unit string
        [$factorial_units, $reciprocal_units] = $this->decompose_composite_unit_string($unitString);

        // Check if this composite unit supports it.
        $supports_all = true;
        foreach ($factorial_units as $unit) {
            $supports_any = false;
            foreach ($this->factorial_units as $supportedUnit) {
                if ($supportedUnit::getInstance()->supports($unit)) {
                    $supports_any = true;
                    break;
                }
            }

            if (!$supports_any) {
                $supports_all = false;
            }
        }

        foreach ($reciprocal_units as $unit) {
            $supports_any = false;
            foreach ($this->reciprocal_units as $supportedUnit) {
                if ($supportedUnit::getInstance()->supports($unit)) {
                    $supports_any = true;
                    break;
                }
            }

            if (!$supports_any) {
                $supports_all = false;
            }
        }

        return $supports_all;
    }

    public function getCompositeConversionFactor(string $unitString): float
    {
        $conversion_factor = 1.0;
        [$factorial_units, $reciprocal_units] = $this->decompose_composite_unit_string($unitString);

        foreach ($factorial_units as $unit) {
            foreach ($this->factorial_units as $supportedFactorialUnit) {
                if ($supportedFactorialUnit::getInstance()->supports($unit)) {
                    $factor = $supportedFactorialUnit::create(1, $unit)->getValue();
                    $conversion_factor *= $factor;
                }
            }
        }

        foreach ($reciprocal_units as $unit) {
            foreach ($this->reciprocal_units as $supportedReciprocalUnit) {
                if ($supportedReciprocalUnit::getInstance()->supports($unit)) {
                    $factor = $supportedReciprocalUnit::create(1, $unit)->getValue();
                    $conversion_factor /= $factor;
                }
            }
        }

        return $conversion_factor;
    }

    public function convertToBaseValue(float $value, ?string $unitString): float
    {
        if (isset($this->unitStringFactors[$unitString])) {
            return $value * $this->unitStringFactors[$unitString];
        }

        // Assumption here is that the CompositeUnit supports the unit!
        $conversion_factor = $this->getCompositeConversionFactor($unitString);

        return $value * $conversion_factor;
    }

    public function convertValueTo(float $value, ?string $unitString): float
    {
        if (isset($this->unitStringFactors[$unitString])) {
            return $value / $this->unitStringFactors[$unitString];
        }

        // Assumption here is that the CompositeUnit supports the unit!
        $conversion_factor = $this->getCompositeConversionFactor($unitString);

        return $value / $conversion_factor;
    }
}