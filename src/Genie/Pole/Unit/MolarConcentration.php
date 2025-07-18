<?php
declare(strict_types=1);

namespace App\Genie\Pole\Unit;

use App\Genie\Pole\CompositeUnit;

class MolarConcentration extends CompositeUnit
{
    public const MOLAR = "M";
    public const MILLIMOLAR = "mM";
    public const MICROMOLAR = "μM";
    public const NANOMOLAR = "nM";
    public const PICOMOLAR = "pM";
    public const FEMTOMOLAR = "fM";
    public const ATTOMOLAR = "aM";
    public const ZEPTOMOLAR = "zM";
    public const YOCTOMOLAR = "yM";

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
