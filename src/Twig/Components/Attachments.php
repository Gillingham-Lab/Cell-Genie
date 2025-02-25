<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\DoctrineEntity\File\File;
use Doctrine\Common\Collections\Collection;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Attachments
{
    /**
     * @var Collection<int, File>
     */
    public Collection $attachments;
}