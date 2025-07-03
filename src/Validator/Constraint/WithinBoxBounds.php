<?php
declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Validator\WithinBoxBoundsValidator;
use Attribute;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_CLASS)]
class WithinBoxBounds extends Constraint
{
    public string $outOfBoundsMessage = "The coordinates are out of bound in box '%box%'.";

    #[HasNamedArguments]
    public function __construct(
        public string $coordinateField,
        public string $boxField,
        ?array $groups = null,
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
        return WithinBoxBoundsValidator::class;
    }
}