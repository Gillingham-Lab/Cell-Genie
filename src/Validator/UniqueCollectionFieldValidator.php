<?php
declare(strict_types=1);

namespace App\Validator;

use App\Validator\Constraint\UniqueCollectionField;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidOptionsException;

class UniqueCollectionFieldValidator extends ConstraintValidator
{
    public function __construct(
        private readonly PropertyAccessorInterface $accessor
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!($constraint instanceof UniqueCollectionField)) {
            throw new UnexpectedTypeException($constraint, UniqueCollectionField::class);
        }

        if (!($value instanceof Collection)) {
            throw new UnexpectedTypeException($value, Collection::class);
        }

        $values = [];
        $i = 0;
        foreach ($value as $entity) {
            if (!$this->accessor->isReadable($entity, $constraint->field)) {
                throw new InvalidOptionsException("{$constraint->field} is not accessible.", []);
            }

            $value = $this->accessor->getValue($entity, $constraint->field);

            if ($value === null) {
                $value = "null";
            }

            if (in_array($value, $values, true)) {
                $errorPath = $constraint->errorPath ?? $constraint->field;
                $this->context->buildViolation($constraint->localMessage)
                    ->setParameter("{{value}}", $value)
                    ->atPath("[{$i}].{$errorPath}")
                    ->addViolation();
            } else {
                $values[] = $value;
            }

            $i++;
        }

        if (count($values) === $i) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->addViolation();
    }
}