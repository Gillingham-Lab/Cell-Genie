<?php
declare(strict_types=1);

namespace App\Entity\Table;

use App\Twig\Components\ColorPreview;

class ColorColumn extends Column
{
    const component = true;

    public function getRender(object $row): mixed
    {
        $color = parent::getRender($row);

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