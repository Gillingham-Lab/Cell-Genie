<?php
declare(strict_types=1);

namespace App\Genie\Pole\Unit;

use App\Genie\Pole\BaseUnit;

class MassConcentration extends BaseUnit
{
    public const KILOGRAMPERLITER = "kg/L";
    public const GRAMPERLITER = "g/L";
    public const MILLIGRAMPERLITER = "mg/L";
    public const MICROGRAMPERLITER = "μg/L";
    public const NANOGRAMPERLITER = "ng/L";

    public const GRAMPERMILLILITER = "g/mL";
    public const MILLIGRAMPERMILLILITER = "mg/mL";
    public const MICROGRAMPERMILLILITER = "μg/mL";
    public const NANOGRAMPERMILLILITER = "ng/mL";

    public const MICROGRAMPERMICROLITER = "μg/μL";
    public const NANOGRAMPERMICROLITER = "ng/μL";

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
