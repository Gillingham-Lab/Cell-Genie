<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Box;
use App\Entity\BoxMap;
use App\Entity\Rack;
use App\Form\Storage\BoxType;
use App\Form\Storage\RackType;
use App\Repository\BoxRepository;
use App\Repository\Cell\CellAliquotRepository;
use App\Repository\RackRepository;
use App\Repository\StockKeeping\ConsumableLotRepository;
use App\Repository\Substance\SubstanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StorageController extends AbstractController
{
    #[Route("/storage", name: "app_storage")]
    #[Route("/storage/location/{rack}", name: "app_storage_view_rack")]
    #[Route("/storage/{box}", name: "app_storage_view_box")]
    public function storageOverview(
        RackRepository $rackRepository,
        BoxRepository $boxRepository,
        SubstanceRepository $substanceRepository,
        CellAliquotRepository $cellAliquotRepository,
        ConsumableLotRepository $consumableLotRepository,
        Rack $rack = null,
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

                $boxMap->add($substanceLot, $numberOfAliquots, $lotCoordinate);
            }

            $cellAliquotsInBox = $cellAliquotRepository->findAllFromBoxes([$box]);

            foreach ($cellAliquotsInBox as $cellAliquot) {
                $numberOfAliquots = $cellAliquot->getVials();
                $lotCoordinate = $cellAliquot->getBoxCoordinate();

                $boxMap->add($cellAliquot, $numberOfAliquots, $lotCoordinate);
            }
        }

        if ($rack) {
            // Add consumables
            $consumablesInRack = $consumableLotRepository->getLotsByLocation($rack);
        }

        return $this->render("parts/storage/storage.html.twig", [
            "racks" => $racks,
            "boxes" => $boxes,
            "box" => $box,
            "boxMap" => $boxMap,
            "rack" => $rack,
            "consumables" => $consumablesInRack ?? [],
        ]);
    }

    #[Route("/storage/box/add", name: "app_storage_add_box")]
    public function addBox(
        Request $request,
        EntityManagerInterface $entityManager,
    ) {
        return $this->addStorage($request, $entityManager, null, new Box());
    }

    #[Route("/storage/box/edit/{box}", name: "app_storage_edit_box")]
    public function editBox(
        Request $request,
        EntityManagerInterface $entityManager,
        Box $box = null,
    ) {
        return $this->addStorage($request, $entityManager, null, $box);
    }

    #[Route("/storage/rack/add", name: "app_storage_add_rack", defaults: ["box" => null, "rack" => null])]
    public function addRack(
        Request $request,
        EntityManagerInterface $entityManager,
    ) {
        return $this->addStorage($request, $entityManager, new Rack(), null);
    }

    #[Route("/storage/rack/edit/{rack}", name: "app_storage_edit_rack", defaults: ["box" => null, "rack" => null])]
    public function editRack(
        Request $request,
        EntityManagerInterface $entityManager,
        Rack $rack,
    ) {
        return $this->addStorage($request, $entityManager, $rack, null);
    }

    public function addStorage(
        Request $request,
        EntityManagerInterface $entityManager,
        ?Rack $rack,
        ?Box $box,
    ): Response {
        $routeName = $request->attributes->get("_route");

        $entity = null;
        $new = false;
        $returnTo = function (Rack|Box $entity) { return $this->generateUrl("app_homepage");};

        if ($routeName === "app_storage_add_box" or $routeName === "app_storage_edit_box") {
            $type = "box";

            if ($box and !$box->getUlid()) {
                $new = true;
            }

            $entity = $box;
            $formType = BoxType::class;


            $returnTo = function(Box $box) {
                if ($box->getUlid()) {
                    return $this->generateUrl("app_storage_view_box", ["box" => $box->getUlid()]);
                } else {
                    return $this->generateUrl("app_storage");
                }
            };
        } elseif ($routeName === "app_storage_add_rack" or $routeName === "app_storage_edit_rack") {
            $type = "rack";

            if ($rack and !$rack->getUlid()) {
                $new = true;
            }

            $entity = $rack;
            $formType = RackType::class;
            $returnTo = function(Rack $rack) {return $this->generateUrl("app_storage");};
        }

        $formOptions = [
            "save_button" => true,
        ];

        $form = $this->createForm($formType, $entity, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            try {
                $entityManager->persist($entity);
                $entityManager->flush();

                if ($type === "rack" and $new === true) {
                    $message = "New location '{$rack->getName()}' was successfully created.";
                } elseif ($type === "rack" and $new === false) {
                    $message = "Location '{$rack->getName()}' was successfully changed.";
                } elseif ($type === "box" and $new === true) {
                    $message = "New box '{$box->getName()}' was successfully created.";
                } elseif ($type === "box" and $new === false) {
                    $message = "Box '{$box->getName()}' was successfully changed.";
                } else {
                    $message = "You successfully did something.";
                }

                $this->addFlash("success", $message);

                return $this->redirect($returnTo($entity));
            } catch (\Exception $e) {
                if ($new) {
                    $message = "Creating the entry was not possible. Reason: {$e->getMessage()}.";
                } else {
                    $message = "Changing the entry was not possible. Reason: {$e->getMessage()}.";
                }

                $this->addFlash("error", $message);
            }
        }

        return $this->renderForm("parts/forms/add_or_edit_storage.html.twig", [
            "type" => $type,
            "rack" => $rack,
            "box" => $box,
            "form" => $form,
            "returnTo" => $returnTo($entity),
        ]);
    }
}