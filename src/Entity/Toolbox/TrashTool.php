<?php
declare(strict_types=1);

namespace App\Entity\Toolbox;

class TrashTool extends Tool
{
    public function __construct(
        string $path,
        string $icon = "fas fa-fw fa-trash-alt",
        string $buttonClass = "btn-warning",
        bool $enabled = true,
        string $tooltip = "Trash",
        bool $confirmationRequired = true,
        string $confirmationText = "Are you sure?",
    ) {
        parent::__construct($path, $icon, $buttonClass, $enabled, $tooltip, $confirmationRequired, $confirmationText);
    }
}