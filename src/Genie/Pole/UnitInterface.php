<?php
declare(strict_types=1);

namespace App\Genie\Pole;

interface UnitInterface
{
    public static function create(float $value, ?string $unitString = null): Quantity;
    public function getBaseUnitSymbol(): string;
    public function supports(string $unitString): bool;
    public function supportsInterconversionFrom(UnitInterface $sourceUnit): bool;
    public function supportsInterconversionTo(UnitInterface $targetUnit): bool;

    public function convertToBaseValue(float $value, ?string $unitString): float;

    public function convertValueTo(float $value, ?string $unitString): float;

    /**
     * Converts
     * @param float $value
     * @return array{0: numeric, 1: string}
     */
    public function convertValueToClosestUnit(float $value): array;

    public function interconvertFrom(Quantity $quantity): Quantity;
    public function interconvertTo(float $value, UnitInterface $targetUnit): Quantity;
}
