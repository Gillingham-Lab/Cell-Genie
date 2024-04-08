<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Doctrine\Common\Collections\Collection;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Attachments
{
    public Collection $attachments;
}