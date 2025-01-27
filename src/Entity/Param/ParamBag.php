<?php
declare(strict_types=1);

namespace App\Entity\Param;

use ArrayAccess;

/**
 * @implements ArrayAccess<string, Param>
 */
class ParamBag implements ArrayAccess
{
    /** @var array<string, Param> */
    public array $paramArray = [];

    /**
     * @param int|string $offset
     * @param null|scalar $default
     * @return Param|null
     */
    public function getParam(int|string $offset, null|string|float|int|bool $default = null): ?Param
    {
        if ($this->offsetExists($offset)) {
            return $this->paramArray[$offset];
        } elseif ($default !== null) {
            return new Param($default);
        } else {
            return null;
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->paramArray);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->paramArray[$offset]->getValue();
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_array($value) or $value instanceof ParamBag) {
            $this->paramArray[$offset] = new Param($value);
        } else {
            $this->paramArray[$offset] = ($value instanceof Param ? $value : new Param($value));
        }
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->paramArray[$offset]);
    }

    /**
     * @return array<string, Param>
     */
    public function getParamArray(): array
    {
        return $this->paramArray;
    }

    /**
     * @param array<string, Param> $paramArray
     * @return $this
     */
    public function setParamArray(array $paramArray): static
    {
        $this->paramArray = $paramArray;
        return $this;
    }

    public function mergeBag(ParamBag $newBag): ParamBag
    {
        $paramArray = array_merge($this->paramArray, $newBag->getParamArray());
        return (new ParamBag())->setParamArray($paramArray);
    }
}
