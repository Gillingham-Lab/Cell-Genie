<?php
declare(strict_types=1);

namespace App\Genie\Pole\Unit;

use App\Genie\Pole\BaseUnit;

class MolarMass extends BaseUnit
{
    const GRAMPERMOLE = "g/mol";
    const KILOGRAMPERMOLE = "kg/mol";
    const MEGAGRAMPERMOLE = "Mg/mol";

    const DALTON = "Da";
    const KILODALTON = "kDa";
    const MEGADALTON = "MDa";

    protected string $base_unit_symbol = self::GRAMPERMOLE;

    protected array $unitStringFactors = [
        self::MEGAGRAMPERMOLE => 1e+6,
        self::KILOGRAMPERMOLE => 1e+3,
        self::GRAMPERMOLE => 1,

        self::MEGADALTON => 1e+6,
        self::KILODALTON => 1e+3,
        self::DALTON => 1,
    ];
}