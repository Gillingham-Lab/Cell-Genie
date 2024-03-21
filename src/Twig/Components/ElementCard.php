<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Twig\Components\Trait\GeneratedIdTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class ElementCard
{
    use GeneratedIdTrait;

    public string $title;
    public ?string $icon = null;
    public ?string $iconStack = null;
    public bool $collapsed = false;
}