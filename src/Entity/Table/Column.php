<?php
declare(strict_types=1);

namespace App\Entity\Table;

use Closure;

class Column
{
    const raw = false;
    const component = false;
    const renderTitle = true;

    public function __construct(
        private readonly string $title,
        private readonly Closure $renderCallback,
        public readonly bool $bold = false,
        private readonly ?Closure $tooltip = null,
        private readonly ?int $widthRecommendation = null,
    ) {

    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getRender(object|array $row, bool $spreadDatum = false): mixed
    {
        if ($spreadDatum and is_array($row)) {
            return ($this->renderCallback)(... $row);
        } else {
            return ($this->renderCallback)($row);
        }
    }

    public function getWidthRecommendation(): ?int
    {
        return $this->widthRecommendation;
    }

    public function getTooltip(): ?Closure
    {
        return $this->tooltip;
    }
}