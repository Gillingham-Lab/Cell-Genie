<?php
declare(strict_types=1);

namespace App\Genie\Pole\Unit;

use App\Genie\Pole\BaseUnit;

class Amount extends BaseUnit
{
    const NONE = null;
    const PERCENT = "%";
    const PERMILLE = "‰";
    const PARTSPERMILLION = "ppm";
    const PARTSPERBILLION = "ppb";
    const PARTSPERTRILLION = "ppt";

    protected string $base_unit_symbol = "";

    protected array $unitStringFactors = [
        self::NONE => 1,
        self::PERCENT => 0.01,
        self::PERMILLE => 0.001,
        self::PARTSPERMILLION => 1e-6,
        self::PARTSPERBILLION => 1e-9,
        self::PARTSPERTRILLION => 1e-12,
    ];
}

#$a = new Quantity(5.422, AmountUnit("μmol"))