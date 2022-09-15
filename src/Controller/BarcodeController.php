<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\Barcode;
use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\FormEntity\BarcodeEntry;
use App\Form\BarcodeType;
use App\Repository\BarcodeRepository;
use App\Repository\Cell\CellCultureRepository;
use App\Repository\Cell\CellRepository;
use App\Repository\Substance\SubstanceRepository;
use App\Service\BarcodeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Ulid;

class BarcodeController extends AbstractController
{
    public function __construct(
        private BarcodeRepository $barcodeRepository,
        private CellCultureRepository $cellCultureRepository,
        private CellRepository $cellRepository,
        private SubstanceRepository $substanceRepository,
    ) {

    }

    #[Route("/barcode/{barcode}", name: "app_barcode")]
    public function redirectBarcode(
        BarcodeService $barcodeService,
        string $barcode
    ) {
        $barcodeEntity = $this->barcodeRepository->findOneBy(["barcode" => $barcode]);

        if ($barcodeEntity) {
            //$barcodeObject = $barcodeService->getObjectFromBarcode($barcodeEntity);

            $routeParams = match($barcodeEntity->getReferencedTable()) {
                CellCulture::class => [
                    "route" => "app_cell_culture_view",
                    "params" => [
                        "cellCulture" => $barcodeEntity->getReferencedId()
                    ]
                ],
                Cell::class => [
                    "route" => "app_cell_view",
                    "params" => [
                        "cellId" => $barcodeEntity->getReferencedId()
                    ]
                ],
                Substance::class => [
                    "route" => "app_substance_view",
                    "params" => [
                        "substance" => $barcodeEntity->getReferencedId(),
                    ]
                ],
            };

            // Redirect to target
            return $this->redirectToRoute($routeParams["route"], $routeParams["params"]);
        } else {
            // Redirect to new
            return $this->redirectToRoute("app_barcode_edit", ["barcode" => $barcode]);
        }
    }

    #[Route("/barcode/edit/{barcode}", name: "app_barcode_edit")]
    public function newBarcode(
        Request $request,
        EntityManagerInterface $em,
        BarcodeService $barcodeService,
        string $barcode
    ) {
        $barcodeEntity = $this->barcodeRepository->findOneBy(["barcode" => $barcode]);

        if ($barcodeEntity === null) {
            $barcodeEntity = new Barcode();
            $barcodeEntity->setBarcode($barcode);
            $newEntry = true;
        } else {
            $newEntry = false;
        }

        $formEntity = new BarcodeEntry($barcode);

        $barcodeService->populateFormEntity($barcodeEntity, $formEntity);

        $form = $this->createForm(BarcodeType::class, $formEntity, ["save_button" => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $barcodeService->populateFromFormEntity($barcodeEntity, $formEntity);

            $em->persist($barcodeEntity);

            $em->flush();

            $this->addFlash("success", "Barcode was registered");
            return $this->redirectToRoute("app_homepage");
        }

        return $this->renderForm("parts/barcodes/barcodes_new.html.twig", [
            "barcode" => $barcode,
            "new" => $newEntry,
            "form" => $form,
        ]);
    }
}