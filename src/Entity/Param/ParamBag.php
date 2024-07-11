<?php
declare(strict_types=1);

namespace App\Entity\Param;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Attribute\Ignore;

class ParamBag implements \ArrayAccess
{
    /** @var Param[] */
    #[Ignore]
    public array $paramArray = [];

    public function getParam($offset, string|float|int|bool $default = null): ?Param
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
        $this->paramArray[$offset] = new Param($value);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->paramArray[$offset]);
    }

    public function getParamArray(): array
    {
        return $this->paramArray;
    }

    public function setParamArray(array $paramArray): static
    {
        $this->paramArray = $paramArray;
        return $this;
    }
}