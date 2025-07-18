<?php
declare(strict_types=1);

namespace App\Twig\Components\Layout;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class SimpleToolbox
{
    public string $size = "md";
    public bool $asDropdown = false;

    public function getGroupSize(): string
    {
        return match ($this->size) {
            "sm" => "btn-group-sm",
            "lg" => "btn-group-lg",
            default => "",
        };
    }
}
