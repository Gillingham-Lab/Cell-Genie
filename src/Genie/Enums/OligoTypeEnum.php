<?php
declare(strict_types=1);

namespace App\Genie\Enums;

enum OligoTypeEnum: string
{
    case Peptide = "peptide";
    case DNA = "DNA";
    case RNA = "RNA";
    case siRNA = "siRNA";
    case LNA = "LNA";
}
