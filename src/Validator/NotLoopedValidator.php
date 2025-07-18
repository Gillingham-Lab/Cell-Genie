<?php
declare(strict_types=1);

namespace App\Validator;

use App\Validator\Constraint\NotLooped;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NotLoopedValidator extends ConstraintValidator
{
    public function __construct(
        private PropertyAccessorInterface $accessor,
    ) {}

    public function validate(mixed $entity, Constraint $constraint): void
    {
        if (!$constraint instanceof NotLooped) {
            throw new UnexpectedTypeException($constraint, NotLooped::class);
        }

        if (!$this->accessor->isReadable($entity, $constraint->parentField)) {
            throw new InvalidOptionsException("{$constraint->parentField} is not accessible", []);
        }

        if (!$this->accessor->isReadable($entity, $constraint->childrenField)) {
            throw new InvalidOptionsException("{$constraint->childrenField} is not accessible", []);
        }

        if ($constraint->maxNestingLevel <= 1) {
            throw new InvalidOptionsException("MaxNestingLevel must at least be 2, but it is set to be '{$constraint->maxNestingLevel}'.", []);
        }

        $currentParent = $this->accessor->getValue($entity, $constraint->parentField);

        if ($currentParent === null) {
            return;
        }

        if ($currentParent === $entity) {
            $this->context->buildViolation($constraint->parentNotInstanceMessage)
                ->atPath($constraint->parentField)
                ->addViolation()
            ;
            return;
        }

        for ($i = 0; $i < $constraint->maxNestingLevel; $i++) {
            if ($currentParent === null) {
                break;
            }

            if ($currentParent === $entity) {
                $this->context->buildViolation($constraint->message)
                    ->atPath($constraint->parentField)
                    ->addViolation()
                ;
                return;
            }

            $currentParent = $this->accessor->getValue($currentParent, $constraint->parentField);
        }

        // Check childdren side

        /** @var Collection<int, mixed> $currentChildren */
        $currentChildren = $this->accessor->getValue($entity, $constraint->childrenField);

        $result = $this->checkChildrenTree($this->accessor->getValue($entity, $constraint->parentField), $constraint, $currentChildren, 0, $constraint->maxNestingLevel);

        if (!$result) {
            $this->context->buildViolation($constraint->childrenMessage)
                ->atPath($constraint->parentField)
                ->addViolation()
            ;
        }
    }

    /**
     * @param Collection<int, mixed> $children
     * @return bool
     */
    private function checkChildrenTree(mixed $entity, NotLooped $constraint, Collection $children, int $level, int $maxLevel = 10): bool
    {
        if ($level === $maxLevel) {
            return true;
        }

        if ($children->count() === 0) {
            return true;
        }

        foreach ($children as $child) {
            if ($child === $entity) {
                return false;
            }

            $subChildren = $this->accessor->getValue($child, $constraint->childrenField);
            $result = $this->checkChildrenTree($entity, $constraint, $subChildren, $level + 1, $maxLevel);

            if (!$result) {
                return false;
            }
        }

        return true;
    }
}
