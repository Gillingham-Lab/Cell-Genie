<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Toolbox\ClipwareTool;
use App\Entity\Toolbox\Tool as ToolEntity;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Tool
{
    public ToolEntity $tool;
    public bool $asDropdown = false;

    public function isClipboard(): bool
    {
        return $this->tool instanceof ClipwareTool;
    }
}
