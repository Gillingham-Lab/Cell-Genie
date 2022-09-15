<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\DoctrineEntity\Barcode;
use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\FormEntity\BarcodeEntry;
use App\Repository\Cell\CellCultureRepository;
use App\Repository\Cell\CellRepository;
use App\Repository\Substance\SubstanceRepository;
use Symfony\Component\Uid\Ulid;

class BarcodeService
{
    public function __construct(
        private CellCultureRepository $cellCultureRepository,
        private CellRepository $cellRepository,
        private SubstanceRepository $substanceRepository,
    ) {

    }

    public function populateFormEntity(Barcode $barcodeEntity, BarcodeEntry $barcodeEntry): void {
        $barcodeEntry->setBarcode($barcodeEntity->getBarcode());

        switch ($barcodeEntity->getReferencedTable()) {
            case CellCulture::class:
                $object = $this->cellCultureRepository->find($barcodeEntity->getReferencedId());
                $barcodeEntry->setCellCulture($object);
                break;

            case Cell::class:
                $object = $this->cellRepository->find($barcodeEntity->getReferencedId());
                $barcodeEntry->setCell($object);
                break;

            case Substance::class:
                $object = $this->substanceRepository->find($barcodeEntity->getReferencedId());
                $barcodeEntry->setSubstance($object);
                break;
        }
    }

    public function populateFromFormEntity(Barcode $barcodeEntity, BarcodeEntry $barcodeEntry): void {
        $barcodeEntity->setBarcode($barcodeEntry->getBarcode());

        if ($barcodeEntry->getCellCulture()) {
            $barcodeEntity->setReferencedTable(CellCulture::class);
            $barcodeEntity->setReferencedId($barcodeEntry->getCellCulture()->getId()->toBase58());
        } elseif ($barcodeEntry->getCell()) {
            $barcodeEntity->setReferencedTable(Cell::class);
            $barcodeEntity->setReferencedId((string)$barcodeEntry->getCell()->getId());
        } elseif ($barcodeEntry->getSubstance()) {
            $barcodeEntity->setReferencedTable(Substance::class);
            $barcodeEntity->setReferencedId($barcodeEntry->getSubstance()->getUlid()->toBase58());
        }
    }
}