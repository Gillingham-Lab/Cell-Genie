<?php
declare(strict_types=1);

namespace App\Pole\Unit;

use App\Pole\BaseUnit;

class Volume extends BaseUnit
{
    const LITER = "L";
    const MILLILITER = "mL";
    const MICROLITER = "μL";
    const NANOLITER = "nL";
    const PICOLITER = "pL";
    const FEMTOLITER = "fL";
    const ATTOLITER = "aL";
    const ZEPTOLITER = "zL";
    const YOCTOLITER = "yL";

    protected string $base_unit_symbol = self::LITER;

    protected array $unitStringFactors = [
        self::LITER => 1,
        self::MILLILITER => 1e-3,
        self::MICROLITER => 1e-6,
        self::NANOLITER => 1e-9,
        self::PICOLITER => 1e-12,
        self::FEMTOLITER => 1e-15,
        self::ATTOLITER => 1e-18,
        self::ZEPTOLITER => 1e-21,
        self::YOCTOLITER => 1e-24,
    ];
}