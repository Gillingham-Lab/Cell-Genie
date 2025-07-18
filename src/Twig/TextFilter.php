<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use function App\mb_str_shorten;

class TextFilter extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter("shorten", mb_str_shorten(...)),
        ];
    }
}
