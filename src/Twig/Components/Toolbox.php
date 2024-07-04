<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Toolbox
{
    public string $size = "md";
    public \App\Entity\Toolbox\Toolbox $toolbox;

    public function getGroupSize(): string
    {
        return match($this->size) {
            "sm" => "btn-group-sm",
            "lg" => "btn-group-lg",
            default => ""
        };
    }
}