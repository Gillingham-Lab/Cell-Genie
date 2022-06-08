<?php
declare(strict_types=1);

namespace App\Pole;

interface UnitInterface
{
    public static function create(float $value, ?string $unitString = null): Quantity;
    public function supports(string $unitString): bool;
    public function supportsInterconversionFrom(UnitInterface $sourceUnit): bool;
    public function supportsInterconversionTo(UnitInterface $targetUnit): bool;

    public function convertToBaseValue(float $value, ?string $unitString): float;
    public function interconvertFrom(Quantity $quantity): Quantity;
    public function interconvertTo(float $value, UnitInterface $targetUnit);
}