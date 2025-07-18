<?php
declare(strict_types=1);

namespace App\Form;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * @implements DataTransformerInterface<null|float, null|string>
 */
readonly class ScientificNumberTransformer implements DataTransformerInterface
{
    public function __construct(
        /** @var string[] */
        private array $nan_values,
        /** @var string[] */
        private array $inf_values,
        /** @var string[] */
        private array $ninf_values,
        private string $nan_value,
        private string $inf_value,
        private string $ninf_value,
    ) {}

    public function transform(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        } elseif (is_string($value)) {
            return $value;
        } elseif (is_nan($value)) {
            return $this->nan_value;
        } elseif (is_infinite($value)) {
            if ($value > 0) {
                return $this->inf_value;
            } else {
                return $this->ninf_value;
            }
        } else {
            return (string) $value;
        }
    }

    public function reverseTransform(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        } else {
            $value = strtolower($value);

            if (in_array($value, array_map(strtolower(...), $this->nan_values))) {
                return NAN;
            } elseif (in_array($value, array_map(strtolower(...), $this->inf_values))) {
                return INF;
            } elseif (in_array($value, array_map(strtolower(...), $this->ninf_values))) {
                return -INF;
            }
        }

        return NAN;
    }
}
