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

        $version_year_a = intval(substr($version_a[0], -4, 4));
        $version_year_b = intval(substr($version_b[0], -4, 4));

        if ($version_year_a !== $version_year_b) {
            return $version_year_a <=> $version_year_b;
        } else {
            return $version_a[1] <=> $version_b[1];
        }
    }
}