<?php
declare(strict_types=1);

namespace App\Entity\Table;

class ToggleColumn extends Column
{
    const raw = true;

    public function getRender(object|array $row, bool $spreadDatum = false): mixed
    {
        $result = parent::getRender($row, $spreadDatum);

        if ($result) {
            return "<span class='fas fa-fw fa-toggle-on fa-2x' aria-valuetext='Yes'></span>";
        } else {
            return "<span class='fas fa-fw fa-toggle-off fa-2x' aria-valuetext='No'></span>";
        }
    }

    public function getWidthRecommendation(): ?int
    {
        return 5;
    }
}