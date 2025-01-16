<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\SequenceAnnotation;
use Doctrine\Common\Collections\Collection;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

/**
 * @phpstan-import-type SequenceAnnotationArray from SequenceAnnotation
 */
#[AsTwigComponent]
class PlasmidViewer
{
    public ?string $sequence;
    public ?int $length;
    /** @var SequenceAnnotation[] */
    public array $annotations;

    /**
     * @param array{sequence?: string, length?: int, annotations: Collection<int, SequenceAnnotation>} $props
     * @return array{sequence?: string, length?: int, annotations: SequenceAnnotation[]}
     */
    #[PreMount]
    public function preMount(array $props): array
    {
        if ($props["annotations"] instanceof Collection) {
            $props["annotations"] = $props["annotations"]->toArray();
        }

        return $props;
    }
}