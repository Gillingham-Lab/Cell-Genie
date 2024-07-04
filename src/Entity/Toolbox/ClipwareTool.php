<?php
declare(strict_types=1);

namespace App\Entity\Toolbox;

class ClipwareTool extends Tool
{
    public function __construct(
        private readonly string $clipboardText,
        string $path = "",
        ?string $icon = "clipboard",
        string $buttonClass = "btn-primary btn-clipboard",
        bool $enabled = true,
        ?string $tooltip = null,
        bool $confirmationRequired = false,
        string $confirmationText = "Are you sure?",
        ?string $iconStack = null,
    ) {
        parent::__construct($path, $icon, $buttonClass, $enabled, $tooltip, $confirmationRequired, $confirmationText, $iconStack);
    }

    public function getClipboardText(): string
    {
        return $this->clipboardText;
    }
}