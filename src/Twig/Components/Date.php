<?php
declare(strict_types=1);

namespace App\Twig\Components;

use DateTime;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Date
{
    public ?DateTime $dateTime = null;
}
