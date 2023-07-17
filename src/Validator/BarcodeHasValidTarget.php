<?php
declare(strict_types=1);

namespace App\Validator;

use App\Entity\FormEntity\BarcodeEntry;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class BarcodeHasValidTarget
{
    public static function validate($object, ExecutionContextInterface $context, $payload): void
    {
        /** @var BarcodeEntry $object */
        $substance = !is_null($object->getSubstance());
        $cell = !is_null($object->getCell());
        $cellCulture = !is_null($object->getCellCulture());
        $lot = !is_null($object->getSubstanceLot());

        $evaluation = (int)$substance + (int)($cell) + (int)$cellCulture + (int)$lot;

        if ($evaluation === 0) {
            $context->buildViolation("You must choose a target of this barcode.")
                ->addViolation();
        } elseif ($evaluation > 1) {
            $context->buildViolation("The target of the barcode must not be ambiguous. Choose only target.")
                ->addViolation();
        }
    }
}