<?php
declare(strict_types=1);

namespace App\Validator;

use App\Validator\Constraint\OrcId;
use App\Validator\Constraint\ValidBoxCoordinate;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OrcIdValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof OrcId) {
            throw new UnexpectedTypeException($constraint, ValidBoxCoordinate::class);
        }

        if ($value === null ||$value === "") {
            return;
        }

        if (strlen($value) !== 19 or preg_match("/^\d{4}-\d{4}-\d{4}-\d{4}$/", $value) === false) {
            $this->context->buildViolation($constraint->invalidOrcid)
                ->addViolation()
            ;
        }
    }
}