<?php
declare(strict_types=1);

namespace App\Twig\Components\Layout;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class NavDropdownItem
{
    public string $href;
    public string $label;
    public ?string $icon = null;
    public ?string $iconStack = null;
}