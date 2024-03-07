<?php
declare(strict_types=1);

namespace App\Twig\Components\Layout;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class NavItem
{
    public string $label;
    public string $href;
    public ?string $icon = null;
    public ?string $iconStack = null;
}