<?php
declare(strict_types=1);

namespace App\Twig\Components\Trait;

use Symfony\UX\LiveComponent\Attribute\LiveProp;

trait ResettableSaveFlagTrait
{
    #[LiveProp]
    public bool $saved = false;

    public function __invoke(): void
    {
        $this->saved = false;
    }
}