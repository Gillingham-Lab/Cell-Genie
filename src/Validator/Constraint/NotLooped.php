<?php
declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Validator\NotLoopedValidator;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_CLASS)]
class NotLooped extends Constraint
{
    public string $message = "The current entity was found in the parental hierarchy.";
    public string $childrenMessage = "The current entity's parent was found in the child hierarchy.";
    public string $parentNotInstanceMessage = "The current entity's parent cannot be the entity itself.";

    #[HasNamedArguments]
    public function __construct(
        public string $parentField,
        public string $childrenField,
        public int $maxNestingLevel = 10,
        array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);
    }

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return NotLoopedValidator::class;
    }
}