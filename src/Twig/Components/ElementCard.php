<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Twig\Components\Trait\GeneratedIdTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent]
class ElementCard
{
    public string $title;
    public ?string $icon = null;
    public ?string $iconStack = null;
    public bool $collapsed = true;
    public bool $noPadding = false;

    #[ExposeInTemplate]
    public function getId()
    {
        return preg_replace("#[^A-Za-z]#", "", $this->title);
    }
}