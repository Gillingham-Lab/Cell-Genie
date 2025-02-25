<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\DoctrineEntity\Epitope;
use App\Service\EntityResolver;
use App\Service\IconService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent]
class EntityReference
{
    /**
     * @var object|array<int, object>|null
     */
    public object|array|null $entity;
    public bool $iterable = false;
    public string $icon;

    public function __construct(
        private readonly IconService $iconService,
        private readonly EntityResolver $entityResolver,
    ) {

    }

    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    #[PreMount]
    public function preMount(array $attributes)
    {
        $entity = $attributes["entity"];

        return [
            "entity" => $entity,
            "iterable" => is_array($entity) || ($entity instanceof \ArrayAccess),
            "icon" => $this->iconService->get($entity) ?? "unknown",
        ];
    }

    #[ExposeInTemplate(name: "href")]
    public function getHref(?object $entity = null): ?string
    {
        if (!$entity) {
            $entity = $this->entity;
        }

        return $this->entityResolver->getPath($entity);
    }

    public function getClass(?object $entity = null): string
    {
        if (!$entity) {
            $entity = $this->entity;
        }

        return match ($this->entityResolver->getEntityClass($entity)) {
            Epitope::class => "bg-warning",
            default => "bg-primary",
        };
    }
}