<?php
declare(strict_types=1);

namespace App\Twig;

use App\Genie\Enums\Availability;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class AvailabilityTest extends AbstractExtension
{
    function getTests(): array
    {
        return [
            new TwigTest("isAvailable", fn(Availability $availability) => $availability === Availability::Available),
            new TwigTest("isNotAvailable", fn(Availability $availability) => $availability === Availability::Empty),
        ];
    }
}