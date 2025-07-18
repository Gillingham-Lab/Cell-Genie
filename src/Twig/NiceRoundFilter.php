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
            new TwigFilter("roundScientifically", function (string|float $x, int $digits = 3) {
                if (is_string($x)) {
                    $x = floatval($x);
                }

                $log = (int) floor(log10($x));
                $roundDigits = ($log - ($digits - 1));

                return round($x, -$roundDigits);
            }),
        ];
    }
}
