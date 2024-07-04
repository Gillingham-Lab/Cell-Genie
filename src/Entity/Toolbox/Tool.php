<?php
declare(strict_types=1);

namespace App\Entity\Toolbox;

use Closure;

class Tool
{
    public function __construct(
        private readonly string $path,
        private readonly ?string $icon = null,
        private readonly string $buttonClass = "btn-primary",
        private readonly bool $enabled = true,
        private readonly ?string $tooltip = null,
        private readonly bool $confirmationRequired = false,
        private readonly string $confirmationText = "Are you sure?",
        private readonly ?string $iconStack = null,
    ) {

    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function getButtonClass(): string
    {
        return $this->buttonClass;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getTooltip(): ?string
    {
        return $this->tooltip;
    }

    public function isConfirmationRequired(): bool
    {
        return $this->confirmationRequired;
    }

    public function getConfirmationText(): string
    {
        return $this->confirmationText;
    }

    public function getIconStack(): ?string
    {
        return $this->iconStack;
    }
}