<?php
declare(strict_types=1);

namespace App\Entity\Param;

use TypeError;

class Param
{
    public function __construct(
        private int|float|bool|string $value,
        private ?ParamTypeEnum $paramType = null,
    ) {
        if ($this->paramType !== null) {
            $this->assertParamType();
        } else {
            $this->paramType = match(get_debug_type($this->value)) {
                "bool" => ParamTypeEnum::Bool,
                "int" => ParamTypeEnum::Int,
                "float" => ParamTypeEnum::Float,
                "string" => ParamTypeEnum::String,
            };
        }
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

    public function asInt(): int
    {
        return (int)$this->getValue();
    }

    public function asFloat(): float
    {
        return (float)$this->getValue();
    }

    public function asString(): string
    {
        return (string)$this->getValue();
    }

    public function asBool(): bool
    {
        return (bool)$this->getValue();
    }

    /**
     * @throws TypeError
     */
    private function assertParamType(null|int|float|bool|string $value=null): void
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
            throw new TypeError("Value type must match ParamType. {$actualType} was given, but {$this->paramType->value} was required.");
        }
    }
}