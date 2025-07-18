<?php
declare(strict_types=1);

namespace App\Genie\Pole\Unit;

use App\Genie\Pole\BaseUnit;

class Mass extends BaseUnit
{
    public const GRAM = "g";

    public const MILLIGRAM = "mg";
    public const MICROGRAM = "μg";
    public const NANOGRAM = "ng";
    public const PICOGRAM = "pg";
    public const FEMTOGRAM = "fg";
    public const ATTOGRAM = "ag";
    public const ZEPTOGRAM = "zg";
    public const YOCTOGRAM = "yg";

    public const KILOGRAM = "kg";
    public const MEGAGRAM = "Mg";
    public const GIGAGRAM = "Gg";
    public const TERAGRAM = "Tg";
    public const PETAGRAM = "Pg";
    public const EXAGRAM = "Eg";
    public const ZETTAGRAM = "Zg";
    public const YOTTAGRAM = "Yg";

    public const TON = " t";
    public const KILOTON = " kt";
    public const MEGATON = " Mt";
    public const GIGATON = " Gt";

    protected string $base_unit_symbol = self::GRAM;

    protected array $unitStringFactors = [
        self::YOTTAGRAM => 1e+24,
        self::ZETTAGRAM => 1e+21,
        self::EXAGRAM => 1e+18,
        self::PETAGRAM => 1e+15,
        self::TERAGRAM => 1e+12,
        self::GIGAGRAM => 1e+9,
        self::MEGAGRAM => 1e+6,
        self::KILOGRAM => 1e+3,
        self::GRAM => 1,
        self::MILLIGRAM => 1e-3,
        self::MICROGRAM => 1e-6,
        self::NANOGRAM => 1e-9,
        self::PICOGRAM => 1e-12,
        self::FEMTOGRAM => 1e-15,
        self::ATTOGRAM => 1e-18,
        self::ZEPTOGRAM => 1e-21,
        self::YOCTOGRAM => 1e-24,

        self::GIGATON => 1e+15,
        self::MEGATON => 1e+12,
        self::KILOTON => 1e9,
        self::TON => 1e+6,
    ];
}
