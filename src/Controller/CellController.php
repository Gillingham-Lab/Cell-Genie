<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Repository\BoxRepository;
use App\Repository\Cell\CellAliquotRepository;
use App\Repository\Cell\CellCultureRepository;
use App\Repository\Cell\CellRepository;
use App\Repository\ChemicalRepository;
use App\Repository\ExperimentTypeRepository;
use App\Repository\ProteinRepository;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class CellController extends AbstractController
{
    public function __construct(
        private CellRepository $cellRepository,
        private BoxRepository $boxRepository,
        private CellAliquotRepository $cellAliquoteRepository,
        private ChemicalRepository $chemicalRepository,
        private ProteinRepository $proteinRepository,
        private ExperimentTypeRepository $experimentTypeRepository,
        private EntityManagerInterface $entityManager,
        private CellCultureRepository $cellCultureRepository,
    ) {

    }

    #[Route("/cells", name: "app_cells")]
    public function cells(): Response
    {
        $cells = $this->cellRepository->getCellsWithAliquotes(
            orderBy: ["cellNumber" => "ASC"]
        );

        return $this->render('parts/cells/cells.html.twig', [
            "cells" => $cells,
        ]);
    }

    #[Route("/cells/view/{cellId}", name: "app_cell_view")]
    #[Route("/cells/view/no/{cellNumber}", name: "app_cell_view_number")]
    #[Route("/cells/view/{cellId}/{aliquoteId}", name: "app_cell_aliquote_view")]
    #[Route("/cells/view/no/{cellNumber}/{aliquoteId}", name: "app_cell_aliquote_view_number")]
    public function cell_overview(
        string $cellId = null,
        string $aliquoteId = null,
        string $cellNumber = null,
    ): Response {
        if ($cellId === null AND $cellNumber === null) {
            throw new NotFoundHttpException();
        }

        if ($cellId) {
            try {
                $cell = $this->cellRepository->find($cellId);
            } catch (ConversionException) {
                $cell = null;
            }
        }

        if ($cellNumber) {
            $cell = $this->cellRepository->findOneBy(["cellNumber" => $cellNumber]);
        }

        if (!$cell) {
            $this->addFlash("error", "Cell was not found");
            return $this->redirectToRoute("app_cells");
        }

        // Get all boxes that contain aliquotes of the current cell line
        $boxes = $this->boxRepository->findByAliquotedCell($cell);

        // Get all aliquotes from those boxes
        $aliquotes = $this->cellAliquoteRepository->findAllFromBoxes($boxes);

        // Create an associative array that makes box.id => aliquotes[]
        $boxAliquotes = [];
        foreach ($aliquotes as $aliquote) {
            $aliquoteBox = $aliquote->getBox();

            if (empty($boxAliquotes[$aliquoteBox->getId()])) {
                $boxAliquotes[$aliquoteBox->getId()] = [];
            }

            $boxAliquotes[$aliquoteBox->getId()][] = $aliquote;
        }

        if ($aliquoteId) {
            $aliquote = $this->cellAliquoteRepository->find($aliquoteId);
        } else {
            $aliquote = null;
        }

        // Get associated chemicals
        $chemicals = $this->chemicalRepository->findByCell($cell);
        // Get associated proteins
        $proteins = $this->proteinRepository->findByCell($cell);
        // Get associated experiment types
        $experimentTypes = $this->experimentTypeRepository->findByCell($cell);

        return $this->render('parts/cells/cell.html.twig', [
            "cell" => $cell,
            "boxes" => $boxes,
            "boxAliquotes" => $boxAliquotes,
            "aliquote" => $aliquote,
            "chemicals" => $chemicals,
            "proteins" => $proteins,
            "experimentTypes" => $experimentTypes,
        ]);
    }

    #[Route("/cells/consume/{aliquoteId}", name: "app_cell_consume_aliquote")]
    public function consumeAliquote($aliquoteId): Response
    {
        $aliquote = $this->cellAliquoteRepository->find($aliquoteId);

        if (!$aliquote) {
            $this->addFlash("error", "Aliquote does not exist.");
            return $this->redirectToRoute("app_cells");
        }

        if ($aliquote->getVials() <= 0) {
            $this->addFlash("error", "There are no aliquote left to consume.");
        } else {
            $aliquote->setVials($aliquote->getVials() - 1);
            $this->entityManager->flush();

            $this->addFlash("success", "Aliquote was consumed.");
        }

        return $this->redirectToRoute("app_cell_aliquote_view", [
            "cellId" => $aliquote->getCell()->getId(),
            "aliquoteId" => $aliquoteId
        ]);
    }

    #[Route("/cells/cultures", name: "app_cell_cultures")]
    public function cellCultures(): Response
    {
        $startDate = new \DateTime("today - 3 weeks");
        $endDate = new \DateTime("today + 1 weeks");
        $currentCultures = $this->cellCultureRepository->findAllBetween($startDate, $endDate);

        $cultures = [];
        /** @var CellCulture $culture */
        foreach ($currentCultures as $culture) {
            if (isset($cultures[$culture->getId()->toBase58()])) {
                continue;
            }

            if ($culture->getParentCellCulture() !== null) {
                continue;
            }

            $cultures[$culture->getId()->toBase58()] = $culture;

            $this->extractCultures($cultures, $culture);
        }

        return $this->render("parts/cells/cell_cultures.html.twig", [
            "cultures" => array_values($cultures),
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);
    }

    private function extractCultures(array &$cultures, CellCulture $culture)
    {
        foreach ($culture->getSubCellCultures() as $subCulture) {
            if (isset($cultures[$subCulture->getId()->toBase58()])) {
                continue;
            }

            $cultures[$subCulture->getId()->toBase58()] = $subCulture;

            $this->extractCultures($cultures, $subCulture);
        }
    }
}
