<?php
declare(strict_types=1);

namespace App\Twig;

use Doctrine\Common\Collections\Collection;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MathFilter extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter("sum", function (array|Collection $numbers) {
                if ($numbers instanceof Collection) {
                    $numbers = $numbers->toArray();
                }

                return array_sum($numbers);
            }),
        ];
    }
}
