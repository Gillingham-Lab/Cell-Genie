<?php
declare(strict_types=1);

namespace App\Genie\Enums;

enum LogType: string
{
    case Minor = "Minor";
    case Major = "Major";
    case Critical = "Critical";
    case Repair = "Repair";
    case Change = "Change";
    case Comment = "Comment";
    case Info = "Info";
    case Normal = "Others";
}
