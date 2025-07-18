<?php
declare(strict_types=1);

namespace App\Entity\Toolbox;

class Tool
{
    /**
     * @param string $path
     * @param string|null $icon
     * @param string $buttonClass
     * @param bool $enabled
     * @param string|null $tooltip
     * @param bool $confirmationRequired
     * @param string $confirmationText
     * @param string|null $iconStack
     * @param array<string, mixed> $otherAttributes
     */
    public function __construct(
        private readonly string $path,
        private readonly ?string $icon = null,
        private readonly string $buttonClass = "btn-primary",
        private readonly bool $enabled = true,
        private readonly ?string $tooltip = null,
        private readonly bool $confirmationRequired = false,
        private readonly string $confirmationText = "Are you sure?",
        private readonly ?string $iconStack = null,
        private readonly array $otherAttributes = [],
    ) {}

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

    /**
     * @return array<string, mixed>
     */
    public function getOtherAttributes(): array
    {
        return $this->otherAttributes;
    }
}
