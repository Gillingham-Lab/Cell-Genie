<?php
declare(strict_types=1);

namespace App\Validator;

use App\Entity\BoxCoordinate;
use App\Validator\Constraint\ValidBoxCoordinate;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValidBoxCoordinateValidator extends ConstraintValidator
{
    public function __construct(
    ) {

    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidBoxCoordinate) {
            throw new UnexpectedTypeException($constraint, ValidBoxCoordinate::class);
        }

        if ($value === null ||$value === "") {
            return;
        }

        try {
            $boxCoordinate = new BoxCoordinate($value);
        } catch (\InvalidArgumentException) {
            $this->context->buildViolation($constraint->invalidCoordinateMessage)
                ->addViolation()
            ;
        }
    }
}