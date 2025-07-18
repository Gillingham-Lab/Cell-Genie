<?php
declare(strict_types=1);

namespace App\Entity\Table;

use App\Twig\Components\Toolbox;

class ToolboxColumn extends Column
{
    public const renderTitle = false;
    public const component = true;

    public function getRender(object|array $row, bool $spreadDatum = false): mixed
    {
        $toolbox = parent::getRender($row, $spreadDatum);

        return [
            "component" => Toolbox::class,
            "props" => [
                "size" => "sm",
                "toolbox" => $toolbox,
            ],
        ];
    }

    public function getWidthRecommendation(): ?int
    {
        return 1;
    }
}
