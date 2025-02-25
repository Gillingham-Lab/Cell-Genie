<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\DoctrineEntity\Barcode;
use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\FormEntity\BarcodeEntry;
use App\Entity\SubstanceLot;
use App\Repository\Cell\CellCultureRepository;
use App\Repository\Cell\CellRepository;
use App\Repository\LotRepository;
use App\Repository\Substance\SubstanceRepository;

class BarcodeService
{
    /**
     * @param SubstanceRepository<Substance> $substanceRepository
     */
    public function __construct(
        private CellCultureRepository $cellCultureRepository,
        private CellRepository $cellRepository,
        private SubstanceRepository $substanceRepository,
        private LotRepository $lotRepository,
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

            case SubstanceLot::class:
                $lotEntity = $this->lotRepository->find($barcodeEntity->getReferencedId());
                $substanceEntity = $this->substanceRepository->findOneByLot($lotEntity);
                $barcodeEntry->setSubstanceLot(new SubstanceLot($substanceEntity, $lotEntity));
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
        } elseif ($barcodeEntry->getSubstanceLot()) {
            $barcodeEntity->setReferencedTable(SubstanceLot::class);
            $barcodeEntity->setReferencedId($barcodeEntry->getSubstanceLot()->getLot()->getId()->toBase58());
        }
    }
}