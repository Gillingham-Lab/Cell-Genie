<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class NiceRoundFilter extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter("roundScientifically", function (float $x) {
                $log = (int)floor(log10($x));
                $digits = 3;
                $roundDigits = ($log - ($digits - 1));

                return round($x, -$roundDigits);
            }),
        ];
    }
}