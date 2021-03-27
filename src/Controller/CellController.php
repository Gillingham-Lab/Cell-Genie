<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\BoxRepository;
use App\Repository\CellAliquoteRepository;
use App\Repository\CellRepository;
use Doctrine\DBAL\Types\ConversionException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CellController extends AbstractController
{
    public function __construct(
        private CellRepository $cellRepository,
        private BoxRepository $boxRepository,
        private CellAliquoteRepository $cellAliquoteRepository,
    ) {

    }

    #[Route("/cells", name: "app_cells")]
    public function cells(): Response
    {
        $cells = $this->cellRepository->findBy(
            [],
            orderBy: ["name" => "ASC"]
        );

        return $this->render('cells.html.twig', [
            "cells" => $cells,
        ]);
    }

    #[Route("/cells/view/{cellId}", name: "app_cell_view")]
    #[Route("/cells/view/{cellId}/{aliquoteId}", name: "app_cell_aliquote_view")]
    public function cell_overview(string $cellId, string $aliquoteId = null): Response
    {
        try {
            $cell = $this->cellRepository->find($cellId);
        } catch (ConversionException) {
            $cell = null;
        }

        if (!$cell) {
            $this->addFlash("error", "Cell was not found");
            return $this->redirectToRoute("app_cells");
        }

        $boxes = $this->boxRepository->findByAliquotedCell($cell);

        if ($aliquoteId) {
            $aliquote = $this->cellAliquoteRepository->find($aliquoteId);
        } else {
            $aliquote = null;
        }

        return $this->render('cell_view.html.twig', [
            "cell" => $cell,
            "boxes" => $boxes,
            "aliquote" => $aliquote,
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
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash("success", "Aliquote was consumed.");
        }

        return $this->redirectToRoute("app_cell_aliquote_view", [
            "cellId" => $aliquote->getCell()->getId(),
            "aliquoteId" => $aliquoteId
        ]);
    }
}
