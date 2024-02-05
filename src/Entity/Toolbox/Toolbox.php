<?php
declare(strict_types=1);

namespace App\Entity\Toolbox;

use Traversable;

class Toolbox implements \IteratorAggregate
{
    public function __construct(
        private array $tools = [],
    ) {

    }

    public function getTools()
    {
        return $this->tools;
    }

    public function getIterator(): Traversable
    {
        foreach ($this->tools as $tool) {
            yield $tool;
        }
    }
}