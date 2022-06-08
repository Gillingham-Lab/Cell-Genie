<?php
declare(strict_types=1);

namespace App\Pole;

use App\Pole\Exception\UnitNotSupportedException;

abstract class BaseUnit implements UnitInterface
{
    protected string $base_unit_symbol = "";
    protected array $unitStringFactors = [];
    protected array $interconversionFactors = [];

    private static array $instance = [];

    protected function __construct()
    {

    }

    public static function getInstance(): static
    {
        if (empty(static::$instance[static::class])) {
            static::$instance[static::class] = new static();
        }

        return static::$instance[static::class];
    }

    public static function create(float $value, ?string $unitString = null): Quantity
    {
        $t = self::getInstance();

        if ($unitString !== null) {
            if (!$t->supports($unitString)) {
                throw new UnitNotSupportedException(get_class($t) . " does not support the unit {$unitString}");
            }

            $baseValue = $t->convertToBaseValue($value, $unitString);
        } else {
            $baseValue = $value;
        }

        return new Quantity($baseValue, $t);
    }

    public function getBaseUnitSymbol(): string
    {
        return $this->base_unit_symbol;
    }

    public function supports(string $unitString): bool
    {
        return array_key_exists($unitString, $this->unitStringFactors);
    }

    public function supportsInterconversionTo(UnitInterface $targetUnit): bool
    {
        return array_key_exists($targetUnit::class, $this->interconversionFactors);
    }

    public function supportsInterconversionFrom(UnitInterface $sourceUnit): bool
    {
        return array_key_exists($sourceUnit::class, $this->interconversionFactors);
    }

    public function convertToBaseValue(float $value, ?string $unitString): float
    {
        return $value * $this->unitStringFactors[$unitString];
    }

    public function convertValueTo(float $value, ?string $unitString): float
    {
        return $value / $this->unitStringFactors[$unitString];
    }

    public function interconvertFrom(Quantity $quantity): Quantity
    {
        if (!$this->supportsInterconversionFrom($quantity->getUnit())) {
            throw new UnitInterconversionNotSupportedException(
                sprintf(
                    "%s does not support the conversion from %s", $this::class, $quantity->getUnit()::class
                )
            );
        }

        return new Quantity($quantity->getValue() / $this->interconversionFactors[$quantity->getUnit()::class], self::getInstance());
    }

    public function interconvertTo(float $value, UnitInterface $targetUnit): Quantity
    {
        if (!$this->supportsInterconversionTo($targetUnit)) {
            throw new UnitInterconversionNotSupportedException(
                sprintf(
                    "%s does not support the conversion to %s",
                    $this::class,
                    $targetUnit::class,
                )
            );
        }

        return new Quantity($value * $this->interconversionFactors[$targetUnit::class], $targetUnit);
    }
}