<?php
declare(strict_types=1);

namespace App\Entity\Table;

use App\Twig\Components\ProgressBar;
use Closure;

class ProgressColumn extends Column
{
    const component = true;

    public function __construct(
        string $title,
        Closure $renderCallback,
        private readonly bool $showNumbers = false,
    ) {
        parent::__construct($title, $renderCallback);
    }

    public function getRender(object|array $row, bool $spreadDatum = false): mixed
    {
        [$current, $max] = parent::getRender($row, $spreadDatum);

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