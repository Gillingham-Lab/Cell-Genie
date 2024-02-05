<?php
declare(strict_types=1);

namespace App\Entity\Table;

class ToolboxColumn extends Column
{
    const renderTitle = false;

    public function getWidthRecommendation(): ?int
    {
        return 1;
    }
}