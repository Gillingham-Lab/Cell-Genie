<?php
declare(strict_types=1);

namespace App\Entity\Table;

use App\Twig\Components\ColorPreview;

class ColorColumn extends Column
{
    const component = true;

    public function getRender(object|array $row, bool $spreadDatum = false): mixed
    {
        $color = parent::getRender($row, $spreadDatum);

        return [
            "component" => ColorPreview::class,
            "props" => [
                "color" => $color,
            ]
        ];
    }

    public function getWidthRecommendation(): ?int
    {
        return 5;
    }
}