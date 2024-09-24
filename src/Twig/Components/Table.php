<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Entity\Table\Table as TableEntity;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent]
class Table
{
    /** @var array{
     *     numberOfRows?: int,
     *     maxNumberOfRows?: int,
     *     columns?: array{
     *         label: string,
     *         type: string,
     *         showLabel: bool,
     *         widthRecommendation: int,
     *         bold: bool,
     *     },
     *     rows?: array<int, array{
     *         value: mixed,
     *         tooltip: mixed,
     *         raw: bool,
     *         component: string,
     *         isActive: bool,
     *         isDisabled: bool,
     *     }>
     * }
     */
    public array $table = [];
    public bool $small = false;

    /**
     * @param $props array{table?: TableEntity, small?: bool}
     * @return array{
     *      table: array{
     *       numberOfRows: int,
     *       maxNumberOfRows: int,
     *       columns: array{
     *           label: string,
     *           type: string,
     *           showLabel: bool,
     *           widthRecommendation: int,
     *           bold: bool,
     *       },
     *       rows: array<int, array{
     *           value: mixed,
     *           tooltip: mixed,
     *           raw: bool,
     *           component: string,
     *           isActive: bool,
     *           isDisabled: bool,
     *       }>
     *     }
     *  }
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