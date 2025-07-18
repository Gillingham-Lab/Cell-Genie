<?php
declare(strict_types=1);

namespace App\Genie\Pole\Unit;

use App\Genie\Pole\BaseUnit;

class Volume extends BaseUnit
{
    public const LITER = "L";
    public const MILLILITER = "mL";
    public const MICROLITER = "μL";
    public const NANOLITER = "nL";
    public const PICOLITER = "pL";
    public const FEMTOLITER = "fL";
    public const ATTOLITER = "aL";
    public const ZEPTOLITER = "zL";
    public const YOCTOLITER = "yL";

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
