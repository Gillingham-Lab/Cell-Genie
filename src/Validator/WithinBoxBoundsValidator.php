<?php
declare(strict_types=1);

namespace App\Validator;

use App\Entity\Box;
use App\Validator\Constraint\WithinBoxBounds;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class WithinBoxBoundsValidator extends ConstraintValidator
{
    public function __construct(
        private PropertyAccessorInterface $accessor,
    ) {

    }

    public function validate(mixed $entity, Constraint $constraint)
    {
        if (!$constraint instanceof WithinBoxBounds) {
            throw new UnexpectedTypeException($constraint, WithinBoxBounds::class);
        }

        if (!$this->accessor->isReadable($entity, $constraint->boxField)) {
            throw new InvalidOptionsException("{$constraint->boxField} is not accessible", []);
        }

        if (!$this->accessor->isReadable($entity, $constraint->coordinateField)) {
            throw new InvalidOptionsException("{$constraint->coordinateField} is not accessible", []);
        }

        $boxValue = $this->accessor->getValue($entity, $constraint->boxField);
        $coordinateValue = $this->accessor->getValue($entity, $constraint->coordinateField);

        if ($boxValue === null) {
            return;
        }

        if ($coordinateValue === null or $coordinateValue === "") {
            return;
        }

        if (!$boxValue instanceof Box) {
            throw new UnexpectedValueException($constraint, Box::class);
        }

        if (!is_string($coordinateValue)) {
            throw new UnexpectedValueException($constraint, "string");
        }

        $maxNumberOfRows = $boxValue->getRows();
        $maxNUmberOfCols = $boxValue->getCols();

        $matches = [];
        $matchReturn = preg_match("#^(?P<row>[A-Z]+)(-?)(?P<col>[0-9]+)$#", $coordinateValue, $matches);

        if ($matchReturn !== 1) {
            return;
        }

        $rowNumber = $this->stringCoordinateToNumber($matches["row"]);
        $colNumber = (int)$matches["col"];

        if ($rowNumber <= 0 or $colNumber <= 0 or $rowNumber > $maxNumberOfRows or $colNumber > $maxNUmberOfCols) {
            $this->context->buildViolation($constraint->outOfBoundsMessage)
                ->setParameters(["%box%" => (string)$boxValue])
                ->atPath($constraint->coordinateField)
                ->addViolation()
            ;
        }
    }

    private function stringCoordinateToNumber($stringCoordinate): int
    {
        $length = strlen($stringCoordinate);
        $number = 0;

        for ($i=0; $i < $length; $i++) {
            $value = 26**($length - $i);
            $letterValue = ord($stringCoordinate[$i])-65;

            $number += $letterValue*$value;
        }

        return $number+1;
    }
}