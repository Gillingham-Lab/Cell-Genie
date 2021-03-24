<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\CellRepository;
use Doctrine\DBAL\Types\ConversionException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class CellController extends AbstractController
{
    public function __construct(
        private CellRepository $cellRepository,
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

    #[Route("/cells/{cellId}", name: "app_cell_view")]
    public function cell_overview(string $cellId): Response
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

        return $this->render('cell_view.html.twig', [
            "cell" => $cell,
        ]);
    }
}
