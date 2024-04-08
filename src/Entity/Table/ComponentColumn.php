<?php
declare(strict_types=1);

namespace App\Entity\Table;

class ComponentColumn extends Column
{
    const component = true;

    public function getRender(object|array $row, bool $spreadDatum = false): mixed
    {
        [$component, $props] = parent::getRender($row, $spreadDatum);

        return [
            "component" => $component,
            "props" => $props,
        ];
    }
}