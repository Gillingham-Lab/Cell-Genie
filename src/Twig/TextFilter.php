<?php
declare(strict_types=1);

namespace App\Twig;

use Symfony\Component\String\UnicodeString;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use function Symfony\Component\String\u;

class TextFilter extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter("shorten", mb_str_shorten(...)),
        ];
    }
}