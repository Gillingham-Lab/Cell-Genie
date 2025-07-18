<?php
declare(strict_types=1);

namespace App\Genie\Pole\Unit;

use App\Genie\Pole\BaseUnit;

class MolarMass extends BaseUnit
{
    public const GRAMPERMOLE = "g/mol";
    public const KILOGRAMPERMOLE = "kg/mol";
    public const MEGAGRAMPERMOLE = "Mg/mol";

    public const DALTON = "Da";
    public const KILODALTON = "kDa";
    public const MEGADALTON = "MDa";

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
