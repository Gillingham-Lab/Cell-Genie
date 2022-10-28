<?php
declare(strict_types=1);

namespace App\Genie\Pole\Unit;

use App\Genie\Pole\CompositeUnit;

class MolarConcentration extends CompositeUnit
{
    const MOLAR = "M";
    const MILLIMOLAR = "mM";
    const MICROMOLAR = "μM";
    const NANOMOLAR = "nM";
    const PICOMOLAR = "pM";
    const FEMTOMOLAR = "fM";
    const ATTOMOLAR = "aM";
    const ZEPTOMOLAR = "zM";
    const YOCTOMOLAR = "yM";

    protected string $base_unit_symbol = self::MOLAR;

    protected array $factorial_units = [MolarAmount::class];
    protected array $reciprocal_units = [Volume::class];

    protected array $unitStringFactors = [
        self::MOLAR => 1,
        self::MILLIMOLAR => 1e-3,
        self::MICROMOLAR => 1e-6,
        self::NANOMOLAR => 1e-9,
        self::PICOMOLAR => 1e-12,
        self::FEMTOMOLAR => 1e-15,
        self::ATTOMOLAR => 1e-18,
        self::ZEPTOMOLAR => 1e-21,
        self::YOCTOMOLAR => 1e-24,
    ];
}