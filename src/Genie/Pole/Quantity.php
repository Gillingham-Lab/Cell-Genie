<?php
declare(strict_types=1);

namespace App\Genie\Pole;

use App\Pole\UnitNotSupportedException;

class Quantity
{
    const FORMAT_NORMAL = 0;
    const FORMAT_SCIENTIFICALLY = 1;
    const FORMAT_ENGINEERING = 2;
    const FORMAT_ADJUST_UNIT = 3;

    protected float $value;
    protected UnitInterface $unit;

    public function __construct(float $value, UnitInterface $unit)
    {
        $this->value = $value;
        $this->unit = $unit;
    }

    public function getValue(): float
    {
        return $this->value;
    }

    public function getUnit(): UnitInterface
    {
        return $this->unit;
    }

    public function isUnit(string $unitClass)
    {
        return $this->unit::class === $unitClass;
    }

    public function getValueAs(?string $unitString): float
    {
        if (!$this->unit->supports($unitString)) {
            throw new UnitNotSupportedException(get_class($this->unit) . " does not support the unit '{$unitString}'.");
        }

        return $this->unit->convertValueTo($this->value, $unitString);
    }

    public function significantDigits(float $value, int $precision): string
    {
        // Taken from https://stackoverflow.com/questions/37618679/format-number-to-n-significant-digits-in-php
        // If the number is 0, we display it as 0.0*[precision-1]
        if ($value == 0) {
            $decimalPlaces = $precision - 1;
        } else {
            $factor = 1;
            if ($value < 0) {
                $factor = -1;
            }

            $decimalPlaces = $precision - (int)floor(log10($value * $factor)) - 1;
        }

        if ($decimalPlaces > 0) {
            return number_format($value, $decimalPlaces);
        } else {
            return (string)round($value, $decimalPlaces);
        }
    }

    public function format(int $precision = 3, int $format = 0): string
    {
       if ($format === self::FORMAT_SCIENTIFICALLY) {
           // Calculate magnitude
           $magnitude = floor(log10($this->value > 0 ? $this->value : -1 * $this->value));

           if ($magnitude == 0) {
               // If magnitude is 0, we do not need to change anything
               return $this->significantDigits($this->value, $precision);
           } else {
               // If magnitude <> 0, we adjust it by the scaling factor and return {scaledValue}e{magnitude}.
               $scalingFactor = pow(10, $magnitude);
               return $this->significantDigits($this->value / $scalingFactor, $precision) . "e{$magnitude}";
           }
       } elseif ($format === self::FORMAT_ENGINEERING) {
           // Calculate magnitude
           $magnitude = (int)floor(log10($this->value > 0 ? $this->value : -1 * $this->value));

           // Engineering should be a multiple of 3
           if ($magnitude > 0) {
               $engMagnitude = intdiv($magnitude, 3);
           } else {
               $engMagnitude = intdiv($magnitude, 3) - 1;
           }

           if ($engMagnitude == 0) {
               // If magnitude is 0, we do not need to change anything
               return $this->significantDigits($this->value, $precision);
           } else {
               // If magnitude <> 0, we adjust it by the scaling factor and return {scaledValue}e{magnitude}.
               $scalingFactor = pow(1000, $engMagnitude);
               $realMagnitude = $engMagnitude * 3;
               return $this->significantDigits($this->value / $scalingFactor, $precision) . "e{$realMagnitude}";
           }
       } elseif ($format === self::FORMAT_ADJUST_UNIT) {
           [$value, $unit] = $this->unit->convertValueToClosestUnit($this->value);

           return $this->significantDigits($value, $precision) . " " . $unit;
       } else {
           return $this->significantDigits($this->value, $precision);
       }
    }
}