<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Pagination
{
    #[Assert\Range(min: 0)]
    public int $currentPage;

    #[Assert\Range(min: 0)]
    public int $lastPage;

    #[Assert\Range(min: 5)]
    public int $limit = 30;
}