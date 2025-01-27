<?php
declare(strict_types=1);

namespace App\Entity\Param;

enum ParamTypeEnum: string
{
    case Int = "int";
    case Float = "float";
    case String = "string";
    case Bool = "bool";
    case Bag = "bag";
}
