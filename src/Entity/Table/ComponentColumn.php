<?php
declare(strict_types=1);

namespace App\Entity\Table;

class ComponentColumn extends Column
{
    const component = true;

    public function getRender(object $row): mixed
    {
        [$component, $props] = parent::getRender($row);

        return [
            "component" => $component,
            "props" => $props,
        ];
    }
}