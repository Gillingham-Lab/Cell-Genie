<?php
declare(strict_types=1);

namespace App\Twig\Components\Layout;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Navbar
{
    public string $id;

    public function getContainerId(): string {
        return "{$this->id}-container";
    }
}