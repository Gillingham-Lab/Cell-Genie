<?php
declare(strict_types=1);

namespace App\Twig\Components\Layout;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Alert
{
    public string $type = "success";
    public string $message;

    public function getIcon(): ?string
    {
        return match ($this->type) {
            "success" => "success",
            "error", "danger" => "danger",
            default => "info",
        };
    }
}