<?php
declare(strict_types=1);

namespace App\Genie\Pole\Unit;

use App\Genie\Pole\BaseUnit;

class MolarAmount extends BaseUnit
{
    const MOL = "mol";
    const MILLIMOL = "mmol";
    const MICROMOL = "μmol";
    const NANOMOL = "nmol";
    const PICOMOL = "pmol";
    const FEMTOMOL = "fmol";
    const ATTOMOL = "amol";
    const ZEPTOMOL = "zmol";
    const YOCTOMOL = "ymol";
    const KILOMOL = "kmol";
    const MEGAMOL = "Mmol";
    const GIGAMOL = "Gmol";

    protected string $base_unit_symbol = self::MOL;

    protected array $unitStringFactors = [
        self::GIGAMOL => 1e+9,
        self::MEGAMOL => 1e+6,
        self::KILOMOL => 1e+3,
        self::MOL => 1,
        self::MILLIMOL => 1e-3,
        self::MICROMOL => 1e-6,
        self::NANOMOL => 1e-9,
        self::PICOMOL => 1e-12,
        self::FEMTOMOL => 1e-15,
        self::ATTOMOL => 1e-18,
        self::ZEPTOMOL => 1e-21,
        self::YOCTOMOL => 1e-24,
    ];

    protected array $interconversionFactors = [
        Amount::class => 6.02214076e23, // Avogadros number
    ];
}