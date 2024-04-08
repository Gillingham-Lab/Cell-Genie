<?php
declare(strict_types=1);

namespace App\Entity\Table;

class Column
{
    const raw = false;
    const component = false;
    const renderTitle = true;

    public function __construct(
        private string $title,
        private \Closure $renderCallback,
        public readonly bool $bold = false,
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
        return null;
    }
}