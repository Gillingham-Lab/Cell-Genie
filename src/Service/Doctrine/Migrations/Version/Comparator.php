<?php
declare(strict_types=1);

namespace App\Service\Doctrine\Migrations\Version;

use Doctrine\Migrations\Version\Version;

class Comparator implements \Doctrine\Migrations\Version\Comparator
{

    public function compare(Version $a, Version $b): int
    {
        if ($a === $b) {
            return 0;
        }

        $version_a = explode("\\", (string)$a);
        $version_b = explode("\\", (string)$b);

        $mainGroupA = $version_a[0] === "DoctrineMigrations2023" ? 1 : 0;
        $mainGroupB = $version_b[0] === "DoctrineMigrations2023" ? 1 : 0;

        if ($mainGroupA !== $mainGroupB) {
            return $mainGroupA <=> $mainGroupB;
        } else {
            return $version_a[1] <=> $version_b[1];
        }
    }
}