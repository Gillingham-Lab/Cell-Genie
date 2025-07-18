<?php
declare(strict_types=1);

namespace App\Twig\Components\Layout;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsTwigComponent]
class Row
{
    public ?int $cols = null;
    public ?int $sm = null;
    public ?int $md = null;
    public ?int $lg = null;
    public ?int $xl = null;
    public ?int $xxl = null;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    #[PreMount]
    public function preMount(array $data): array
    {
        $fields = ["cols", "sm", "md", "lg", "xl", "xxl"];

        foreach ($fields as $field) {
            if (isset($data[$field]) && $data[$field] > 6) {
                $data[$field] = 6;
            }
        }

        return $data;
    }

    public function getColumnClass(): string
    {
        $classes = [];

        if ($this->cols) {
            $classes[] = "row-cols-" . $this->cols;
        }

        if ($this->sm) {
            $classes[] = "row-cols-sm-" . $this->sm;
        }

        if ($this->md) {
            $classes[] = "row-cols-md-" . $this->md;
        }

        if ($this->lg) {
            $classes[] = "row-cols-lg-" . $this->lg;
        }

        if ($this->xl) {
            $classes[] = "row-cols-xl-" . $this->xl;
        }

        if ($this->xxl) {
            $classes[] = "row-cols-xxl-" . $this->xxl;
        }

        return implode(" ", $classes);
    }
}
