<?php
declare(strict_types=1);

namespace App;

// Required for phpstan ... ?
class functions {}

if (!function_exists("App\\mb_str_shorten")) {
    function mb_str_shorten(string $string, int $length, string $encoding = "UTF-8"): string
    {
        $partLengths = intdiv($length, 2);

        if (mb_strlen($string, $encoding) > $length) {
            $beginning = mb_substr($string, 0, $partLengths, $encoding);
            $end = mb_substr($string, -$partLengths, null, $encoding);

            return "{$beginning}…{$end}";
        } else {
            return $string;
        }
    }
}
