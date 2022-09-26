<?php
declare(strict_types=1);

namespace App\Genie\Enums;

enum AntibodyType: string
{
    case Primary = "primary";
    case Secondary = "secondary";
}