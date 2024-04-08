<?php
declare(strict_types=1);

namespace App\Genie\Enums;

enum FileType: string
{
    case Any = "any";
    case PowerPoint = "powerpoint";
    case Word = "word";
    case Excel = "excel";
    case Pdf = "pdf";
}