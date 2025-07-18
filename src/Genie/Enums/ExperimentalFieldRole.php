<?php
declare(strict_types=1);

namespace App\Genie\Enums;

enum ExperimentalFieldRole: string
{
    case Top = "0_top";
    case Condition = "1_condition";
    case Comparison = "2_comparison";
    case Datum = "3_datum";
}
