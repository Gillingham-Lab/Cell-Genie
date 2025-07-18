<?php
declare(strict_types=1);

namespace App\Entity\Toolbox;

use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, Tool>
 */
class Toolbox implements IteratorAggregate
{
    /**
     * @param Tool[] $tools
     */
    public function __construct(
        private array $tools = [],
    ) {}

    /**
     * @return Tool[]
     */
    public function getTools()
    {
        return $this->tools;
    }

    /**
     * @return Traversable<Tool>
     */
    public function getIterator(): Traversable
    {
        foreach ($this->tools as $tool) {
            yield $tool;
        }
    }
}
