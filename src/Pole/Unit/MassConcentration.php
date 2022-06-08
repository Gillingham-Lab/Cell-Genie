<?php
declare(strict_types=1);

namespace App\Pole\Unit;

use App\Pole\BaseUnit;

class MassConcentration extends BaseUnit
{
    const KILOGRAMPERLITER = "kg/L";
    const GRAMPERLITER = "g/L";
    const MILLIGRAMPERLITER = "mg/L";
    const MICROGRAMPERLITER = "μg/L";
    const NANOGRAMPERLITER = "ng/L";

    const GRAMPERMILLILITER = "g/mL";
    const MILLIGRAMPERMILLILITER = "mg/mL";
    const MICROGRAMPERMILLILITER = "μg/mL";
    const NANOGRAMPERMILLILITER = "ng/mL";

    const MICROGRAMPERMICROLITER = "μg/μL";
    const NANOGRAMPERMICROLITER = "ng/μL";

    protected string $base_unit_symbol = self::GRAMPERLITER;

    protected array $unitStringFactors = [
        self::KILOGRAMPERLITER => 1e+3,
        self::GRAMPERLITER => 1,
        self::MILLIGRAMPERLITER => 1e-3,
        self::MICROGRAMPERLITER => 1e-6,
        self::NANOGRAMPERLITER => 1e-9,

        self::GRAMPERMILLILITER => 1e+3,
        self::MILLIGRAMPERMILLILITER => 1,
        self::MICROGRAMPERMILLILITER => 1e-3,
        self::NANOGRAMPERMILLILITER => 1e-6,

        self::MICROGRAMPERMICROLITER => 1,
        self::NANOGRAMPERMICROLITER => 1e-3,
    ];
}