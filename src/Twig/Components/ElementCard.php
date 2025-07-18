<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Toolbox\Toolbox as ToolboxEntity;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent]
class ElementCard
{
    public string $title;
    public ?string $icon = null;
    public ?string $iconStack = null;
    public bool $collapsed = false;
    public bool $noPadding = false;
    public ?ToolboxEntity $toolbox = null;

    #[ExposeInTemplate]
    public function getId(): string
    {
        return preg_replace("#[^A-Za-z]#", "", $this->title);
    }
}
