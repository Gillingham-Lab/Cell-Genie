<?php
declare(strict_types=1);

namespace App\Genie\Enums;

enum ExperimentalFieldRole: string
{
    case Top = "top";
    case Condition = "condition";
    case Datum = "datum";
}