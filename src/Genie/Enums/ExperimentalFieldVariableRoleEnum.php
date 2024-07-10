<?php
declare(strict_types=1);

namespace App\Genie\Enums;

enum ExperimentalFieldVariableRoleEnum: string
{
    case Group = "group";
    case X = "x";
    case Y = "y";
    case Xerr = "xerr";
    case Yerr = "yerr";
}
