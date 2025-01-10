<?php
declare(strict_types=1);

namespace App\Genie\Enums;

enum IntegerTypeEnum: int
{
    case Int8 = 1;
    case Int16 = 2;
    case Int32 = 4;
    case Int64 = 8;
}
