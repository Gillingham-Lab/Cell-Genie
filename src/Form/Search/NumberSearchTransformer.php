<?php
declare(strict_types=1);

namespace App\Form\Search;

use Symfony\Component\Form\DataTransformerInterface;


class ShieldedFloat {
    public readonly ?float $floatValue;
    public readonly bool $isInfinite;
    public readonly ?int $infiniteSign;

    public function __construct(float $float) {
        if (is_nan($float)) {
            $this->floatValue = null;
            $this->isInfinite = false;
            $this->infiniteSign = null;
        } elseif (is_infinite($float)) {
            $this->floatValue = null;
            $this->isInfinite = true;

            $this->infiniteSign = $float <=> 0;
        } else {
            $this->floatValue = $float;
            $this->infiniteSign = null;
            $this->isInfinite = false;
        }
    }

    public function getFloatValue(): float
    {
        if ($this->floatValue === null) {
            if ($this->isInfinite) {
                if ($this->infiniteSign > 0) {
                    return -INF;
                } else {
                    return INF;
                }
            } else {
                return NAN;
            }
        } else {
            return $this->floatValue;
        }
    }
}


class NumberSearchTransformer implements DataTransformerInterface
{
    private function shield(float $floatValue): string|float
    {
        if (is_nan($floatValue)) {
            return "";
        } elseif (is_infinite($floatValue)) {
            if ($floatValue > 0) {
                return "Inf";
            } else {
                return "-Inf";
            }
        } else {
            return $floatValue;
        }
    }

    private function unshield(float|string $shieldedFloat): float
    {
        if ($shieldedFloat === "") {
            return NAN;
        } elseif (is_string($shieldedFloat) && strtolower($shieldedFloat) === "inf") {
            return INF;
        } elseif (is_string($shieldedFloat) && strtolower($shieldedFloat) === "-inf") {
            return -INF;
        } else {
            return floatval($shieldedFloat);
        }
    }

    public function reverseTransform(mixed $value)
    {
        $return = $value;

        if (isset($value["min"])) {
            $return["min"] = $this->shield($value["min"]);
        }

        if (isset($value["max"])) {
            $return["max"] = $this->shield($value["max"]);
        }

        return $return;
    }

    public function transform(mixed $value)
    {
        $return = $value;

        if (isset($value["min"])) {
            $return["min"] = $this->unshield($value["min"]);
        }

        if (isset($value["max"])) {
            $return["max"] = $this->unshield($value["max"]);
        }

        return $return;
    }
}