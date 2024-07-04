<?php
declare(strict_types=1);

namespace App\Repository\Traits;

trait SearchTermTrait
{
    private function prepareSearchTerm($searchTerm)
    {
        if (!str_starts_with($searchTerm, "%") and !str_starts_with($searchTerm, "^")) {
            $searchTerm = "%" . $searchTerm;
        }

        if (!str_ends_with($searchTerm, "%") and !str_starts_with($searchTerm, "$")) {
            $searchTerm = $searchTerm . "%";
        }

        return $searchTerm;
    }
}