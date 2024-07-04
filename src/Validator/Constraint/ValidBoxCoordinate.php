<?php
declare(strict_types=1);

namespace App\Validator\Constraint;

use App\Validator\ValidBoxCoordinateValidator;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ValidBoxCoordinate extends Constraint
{
    public string $invalidCoordinateMessage = "The given coordinates are invalid.";

    public function __construct(
        array $groups = null,
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
        return ValidBoxCoordinateValidator::class;
    }
}