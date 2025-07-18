<?php
declare(strict_types=1);

namespace App\Validator;

use App\Validator\Constraint\ValidExpression;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ExpressionValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValidExpression) {
            throw new UnexpectedTypeException($constraint, ValidExpression::class);
        }

        if ($value === null || $value === "") {
            return;
        }

        $expression = new ExpressionLanguage();

        try {
            $expression->lint($value, $constraint->environment);
        } catch (SyntaxError $e) {
            $this->context->buildViolation($constraint->invalidExpressionMessage . " " . $e->getMessage())->addViolation();
        }
    }
}
