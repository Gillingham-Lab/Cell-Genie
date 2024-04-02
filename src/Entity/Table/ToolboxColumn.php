<?php
declare(strict_types=1);

namespace App\Entity\Table;

use App\Twig\Components\Toolbox;

class ToolboxColumn extends Column
{
    const renderTitle = false;
    const component = true;

    public function getRender(object $row): mixed
    {
        $toolbox = parent::getRender($row);

        return [
            "component" => Toolbox::class,
            "props" => [
                "size" => "sm",
                "toolbox" => $toolbox,
            ]
        ];
    }

    public function getWidthRecommendation(): ?int
    {
        return 1;
    }
}