<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Box;
use App\Entity\BoxMap;
use App\Repository\BoxRepository;
use App\Repository\Cell\CellAliquotRepository;
use App\Repository\LotRepository;
use App\Repository\RackRepository;
use App\Repository\Substance\SubstanceRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StorageController extends AbstractController
{
    #[Route("/storage", name: "app_storage")]
    #[Route("/storage/{box}", name: "app_storage_view_box")]
    public function storageOverview(
        RackRepository $rackRepository,
        BoxRepository $boxRepository,
        SubstanceRepository $substanceRepository,
        CellAliquotRepository $cellAliquotRepository,
        Box $box = null,
    ): Response {
        $racks = $rackRepository->findAllWithBoxes();
        $boxes = $boxRepository->findAll();
        $boxMap = $box ? BoxMap::fromBox($box) : null;

        if ($box) {
            // Add lots
            $lotsInBox = $substanceRepository->findAllSubstanceLotsInBox($box);

            foreach ($lotsInBox as $substanceLot) {
                $numberOfAliquots = $substanceLot->getLot()->getNumberOfAliquotes();
                $lotCoordinate = $substanceLot->getLot()->getBoxCoordinate();

                $this->addToBox($boxMap, $substanceLot, $numberOfAliquots, $lotCoordinate);
            }

            // ToDo: Add cells
            $cellAliquotsInBox = $cellAliquotRepository->findAllFromBoxes([$box]);

            foreach ($cellAliquotsInBox as $cellAliquot) {
                $numberOfAliquots = $cellAliquot->getVials();
                $lotCoordinate = $cellAliquot->getBoxCoordinate();

                $this->addToBox($boxMap, $cellAliquot, $numberOfAliquots, $lotCoordinate);
            }
        }

        return $this->render("parts/storage/storage.html.twig", [
            "racks" => $racks,
            "boxes" => $boxes,
            "box" => $box,
            "boxMap" => $boxMap,
        ]);
    }

    private function addToBox(BoxMap $boxMap, object $object, int $numberOfAliquots, ?string $lotCoordinate) {
        // Do not display lots with no aliquots.
        if ($numberOfAliquots === 0) {
            return;
        }

        // If no coordinate is given, add loose.
        if (empty($lotCoordinate)) {
            for ($i=0; $i < $numberOfAliquots; $i++) {
                $boxMap->addLoose($object);
            }
        } else {
            for ($i = 0; $i < $numberOfAliquots; $i++) {
                // Try to set at coordinate. If it fails, add loose.
                try {
                    $boxMap->setAtCoordinate($lotCoordinate, $object, shift: $i);
                } catch (\InvalidArgumentException) {
                    $boxMap->addLoose($object);
                }
            }
        }
    }
}