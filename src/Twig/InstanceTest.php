<?php
declare(strict_types=1);

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class InstanceTest extends AbstractExtension
{
    function getTests(): array
    {
        return [
            new TwigTest("instanceof", fn(mixed $var, $instance) => $var instanceof $instance),
            new TwigTest("isarray", fn(mixed $var) => is_array($var)),
        ];
    }
}