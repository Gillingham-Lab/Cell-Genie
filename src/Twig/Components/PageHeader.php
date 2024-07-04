<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class PageHeader
{
    public string $title;
    public ?string $subTitle = null;
    public ?string $icon = null;
    public ?string $iconStack = null;

    public bool $barcode = false;
}