<?php
declare(strict_types=1);

namespace App\Twig\Components\Layout;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class NavDropdown
{
    public string $id;
    public string $icon;
    public ?string $iconStack = null;
    public string $label;
    public ?string $href = null;

    public function getDropdownId(): string
    {
        return "{$this->id}-link";
    }
}
