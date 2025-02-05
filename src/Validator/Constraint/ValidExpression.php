<?php
declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Validator\ExpressionValidator;
use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ValidExpression extends Constraint
{
    public string $invalidExpressionMessage = "The given expression is invalid.";
    public string $unknownVariableExpressionMessage = "The given expression contains an unknown variable.";

    public function __construct(
        /** @var array<int, string> */
        public readonly array $environment = [],
        ?array $groups = null,
        mixed $payload = null
    ) {
        parent::__construct([], $groups, $payload);
    }

    public function getTargets(): string|array
    {
        return self::PROPERTY_CONSTRAINT;
    }

    public function validatedBy(): string
    {
        return ExpressionValidator::class;
    }
}