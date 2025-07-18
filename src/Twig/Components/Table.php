<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Table\Table as TableEntity;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

/**
 * @phpstan-import-type ArrayTableShape from TableEntity
 */
#[AsTwigComponent]
class Table
{
    /** @var ArrayTableShape|array{} */
    public array $table = [];
    public bool $small = false;

    /**
     * @param array{table?: TableEntity<mixed>, small?: bool} $props
     * @return array{table?: ArrayTableShape, small?: bool}
     */
    #[PreMount]
    public function preMount(array $props): array
    {
        if ($props["table"] instanceof TableEntity) {
            $props["table"] = $props["table"]->toArray();
        }

        return $props;
    }
}
