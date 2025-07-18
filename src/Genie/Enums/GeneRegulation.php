<?php
declare(strict_types=1);

namespace App\Genie\Enums;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum GeneRegulation: string implements TranslatableInterface
{
    case Unknown = "unknown";
    case Normal = "normal";
    case Up = "up";
    case Down = "down";
    case OnOneAllel = "one";
    case OnTwoAllels = "two";
    case KnockIn = "in";
    case KnockOut = "out";
    case DoubleKnockIn = "doublein";
    case DoubleKnockOut = "doubleout";

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return match ($this) {
            self::Unknown => "unknown",
            self::Normal => "normal",
            self::Up => "upregulated",
            self::Down => "downregulated",
            self::KnockIn => "knocked in on one allele",
            self::KnockOut => "knocked out from one allele",
            self::DoubleKnockIn => "knocked in on both alleles",
            self::DoubleKnockOut => "knocked out from both allele",
            self::OnOneAllel => "present on one allele",
            self::OnTwoAllels => "present on both alleles",
        };
    }
}
