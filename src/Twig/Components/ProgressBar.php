<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

/**
 * @phpstan-type ProgressBarColor null|"success"|"warning"|"danger"|"optional"
 * @phpstan-type SubProgressBarProps array{current: numeric, showNumbers: bool, color: ProgressBarColor, striped: bool}
 */
#[AsTwigComponent]
class ProgressBar
{
    public float|int $current;
    public float|int $max;
    public bool $showNumbers = true;
    public ?string $color = null;
    public bool $striped = false;

    /**
     * @var SubProgressBarProps[]
     */
    public array $subBars = [];

    public function getColorClass(?string $color = null): string
    {
        return match($color ?? $this->color) {
            "success" => "text-bg-success",
            "warning" => "text-bg-warning",
            "danger" => "text-bg-danger",
            "optional" => "text-bg-info",
            default => "text-bg-primary",
        };
    }
}