<?php
declare(strict_types=1);

namespace App\Genie;

use IteratorAggregate;
use Traversable;

/**
 * @implements IteratorAggregate<int, string>
 */
class SequenceIterator implements IteratorAggregate
{
    public function __construct(
        private string $sequence,
    ) {

    }

    public function getIterator(): Traversable
    {
        $inComplex = false;
        $buffer = "";

        for ($i = 0; $i < strlen($this->sequence); $i++) {
            $l = $this->sequence[$i];

            if ($inComplex) {
                if ($l === "]") {
                    // The complex notation gets closed - we do not add ] to the buffer, but yield it instead and empty it.
                    $inComplex = false;
                    yield $buffer;

                    $buffer = "";
                } else {
                    // Complex notation is still going on, we fill the buffer.
                    $buffer .= $l;
                }
            } else {
                if ($l === "[") {
                    // Start complex notation. Do not add [, this is part of the syntax.
                    $inComplex = true;
                } elseif($l === "*" or $l === "p" or $l === ".") {
                    // * (thiophosphate), p or . (phosphates) are not part of the sequence and thus are skipped.
                    continue;
                } else {
                    // Not in complex, not complex-opener - lets yield the letter directly.
                    yield $l;
                }
            }
        }
    }
}