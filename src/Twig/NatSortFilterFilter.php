<?php
declare(strict_types=1);

namespace App\Twig;

use Doctrine\Common\Collections\Collection;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class NatSortFilterFilter extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter("natsort", function (Collection|iterable $a) {
                $b = [];

                foreach ($a as $value) {
                    $b[] = $value;
                }

                natsort($b);
                return $b;
            }),
            new TwigFilter("natcasesort", function (Collection|iterable $a) {
                $b = [];

                foreach ($a as $value) {
                    $b[] = $value;
                }

                natcasesort($b);
                return $b;
            }),
        ];
    }
}
