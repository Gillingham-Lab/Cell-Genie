<?php
declare(strict_types=1);

namespace App\Genie\Enums;

enum Availability: string
{
    case Available = "available";
    case Empty = "empty";
    case Ordered = "ordered";
    case InPreparation = "in preparation";
}
