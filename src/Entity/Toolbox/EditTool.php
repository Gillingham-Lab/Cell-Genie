<?php
declare(strict_types=1);

namespace App\Entity\Toolbox;

class EditTool extends Tool
{
    public function __construct(
        string $path,
        string $icon = "edit",
        string $buttonClass = "btn-primary",
        bool $enabled = true,
        string $tooltip = "Edit",
        bool $confirmationRequired = false,
        string $confirmationText = "Are you sure?",
        ?string $iconStack = null,
    ) {
        parent::__construct($path, $icon, $buttonClass, $enabled, $tooltip, $confirmationRequired, $confirmationText, $iconStack);
    }
}