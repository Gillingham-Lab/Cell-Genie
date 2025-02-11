<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class UncertainFloat
{
    public float|string $value;
    public float|string $stderr;
    public null|float|string $lower;
    public null|float|string $upper;
    public ?float $ci;
}