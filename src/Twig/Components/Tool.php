<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Toolbox\Tool as ToolEntity;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;

#[AsTwigComponent]
class Tool
{
    public ToolEntity $tool;
    public bool $asDropdown = false;
}