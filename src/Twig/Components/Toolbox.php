<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Toolbox\Toolbox as ToolboxEntity;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Toolbox
{
    public string $size = "md";
    public ToolboxEntity $toolbox;
    public bool $asDropdown = false;

    public function getGroupSize(): string
    {
        return match($this->size) {
            "sm" => "btn-group-sm",
            "lg" => "btn-group-lg",
            default => ""
        };
    }
}