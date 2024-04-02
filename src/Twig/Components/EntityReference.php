<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Service\IconService;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Collection\CollectionInterface;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent]
class EntityReference
{
    public object|array|null $entity;
    public bool $iterable = false;
    public string $icon;

    public function __construct(
        private IconService $iconService,
    ) {

    }

    #[PreMount]
    public function preMount($attributes)
    {
        $entity = $attributes["entity"];

        return [
            "entity" => $entity,
            "iterable" => is_array($entity) || $entity instanceof \ArrayAccess,
            "icon" => $this->iconService->get($entity) ?? "unknown",
        ];
    }
}