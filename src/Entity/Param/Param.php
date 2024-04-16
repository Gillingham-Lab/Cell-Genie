<?php
declare(strict_types=1);

namespace App\Entity\Param;

class Param
{
    public function __construct(
        private int|float|bool|string $value,
        private readonly ParamTypeEnum $paramType,
    ) {
        $this->assertParamType();
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function setValue(int|float|bool|string $value): void
    {
        $this->assertParamType($value);
        $this->value = $value;
    }

    public function getStringValue(): string
    {
        return $this->value;
    }

    public function getIntValue(): int
    {
        return $this->value;
    }

    public function getFloatValue(): float
    {
        return $this->value;
    }

    public function getBoolValue(): bool
    {
        return $this->value;
    }

    private function assertParamType(null|int|float|bool|string $value=null)
    {
        $throw = false;

        if ($value === null) {
            $value = $this->value;
        }

        switch ($this->paramType) {
            case ParamTypeEnum::Bool:
                if (!is_bool($value)) {
                    $throw = true;
                }
                break;

            case ParamTypeEnum::Float:
                if (!is_float($value)) {
                    $throw = true;
                }
                break;

            case ParamTypeEnum::Int:
                if (!is_int($value)) {
                    $throw = true;
                }
                break;

            case ParamTypeEnum::String:
                if (!is_string($value)) {
                    $throw = true;
                }
                break;
        }

        if ($throw) {
            $actualType = get_debug_type($value);
            throw new \TypeError("Value type must match ParamType. {$actualType} was given, but {$this->paramType->value} was required.");
        }
    }
}