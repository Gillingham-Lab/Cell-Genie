<?php
declare(strict_types=1);

namespace App\Entity\Table;

class Column
{
    const raw = false;
    const renderTitle = true;

    public function __construct(
        private string $title,
        private \Closure $renderCallback,
    ) {

    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getRender(object $row): mixed
    {
        return ($this->renderCallback)($row);
    }

    public function getWidthRecommendation(): ?int
    {
        return null;
    }
}