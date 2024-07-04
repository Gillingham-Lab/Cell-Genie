<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ProgressBar
{
    public int $current;
    public int $max;
    public bool $showNumbers;
}