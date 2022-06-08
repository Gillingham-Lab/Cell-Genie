<?php
declare(strict_types=1);

namespace App\Pole\Unit;

use App\Pole\BaseUnit;

class Mass extends BaseUnit
{
    const GRAM = "g";

    const MILLIGRAM = "mg";
    const MICROGRAM = "μg";
    const NANOGRAM = "ng";
    const PICOGRAM = "pg";
    const FEMTOGRAM = "fg";
    const ATTOGRAM = "ag";
    const ZEPTOGRAM = "zg";
    const YOCTOGRAM = "yg";

    const KILOGRAM = "kg";
    const MEGAGRAM = "Mg";
    const GIGAGRAM = "Gg";
    const TERAGRAM = "Tg";
    const PETAGRAM = "Pg";
    const EXAGRAM = "Eg";
    const ZETTAGRAM = "Zg";
    const YOTTAGRAM = "Yg";

    const TON = " t";
    const KILOTON = " kt";
    const MEGATON = " Mt";
    const GIGATON = " Gt";

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