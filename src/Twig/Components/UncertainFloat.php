<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class UncertainFloat
{
    public null|float|string $value;
    public null|float|string $stderr;
    public null|float|string $lower;
    public null|float|string $upper;
    public ?float $ci;
}