<?php
declare(strict_types=1);

namespace App\Validator;

use App\Entity\Box;
use App\Validator\Constraint\ValidBoxCoordinate;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class ValidBoxCoordinateValidator extends ConstraintValidator
{
    public function __construct(
    ) {

    }

    public function validate(mixed $value, Constraint $constraint)
    {
        if (!$constraint instanceof ValidBoxCoordinate) {
            throw new UnexpectedTypeException($constraint, ValidBoxCoordinate::class);
        }

        if ($value === null ||$value === "") {
            return;
        }

        $matchReturn = preg_match("#^(?P<row>[A-Z]+)(-?)(?P<col>[0-9]+)$#", $value, $matches);

        if ($matchReturn !== 1) {
            $this->context->buildViolation($constraint->invalidCoordinateMessage)
                ->addViolation()
            ;

            return;
        }
    }
}