<?php
declare(strict_types=1);

namespace App\Entity\Interface;

/**
 * @template T of object
 */
interface ChildParentInterface
{
    /**
     * @return iterable<T>
     */
    public function getChildren(): iterable;

    /**
     * @return T|null
     */
    public function getParent(): ?object;

    public function __toString(): string;
}
