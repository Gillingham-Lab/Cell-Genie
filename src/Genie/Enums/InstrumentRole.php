<?php
declare(strict_types=1);

namespace App\Genie\Enums;

enum InstrumentRole: string
{
    case Untrained = "untrained";
    case Trained = "trained";
    case User = "user";
    case Advanced = "advanced";
    case Responsible = "responsible";
    case Admin = "admin";
}
