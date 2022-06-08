<?php
declare(strict_types=1);

namespace App\Pole;

class Quantity
{
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
}