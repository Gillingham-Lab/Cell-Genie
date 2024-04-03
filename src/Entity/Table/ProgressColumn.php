<?php
declare(strict_types=1);

namespace App\Entity\Table;

use App\Twig\Components\ProgressBar;

class ProgressColumn extends Column
{
    const component = true;

    public function __construct(
        string $title,
        \Closure $renderCallback,
        private readonly bool $showNumbers = false,
    ) {
        parent::__construct($title, $renderCallback);
    }

    public function getRender(object $row): mixed
    {
        [$current, $max] = parent::getRender($row);

        return [
            "component" => ProgressBar::class,
            "props" => [
                "current" => $current,
                "max" => $max,
                "showNumbers" => $this->showNumbers,
            ]
        ];
    }

    public function getWidthRecommendation(): ?int
    {
        return 20;
    }
}