<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\ChemicalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CompoundController extends AbstractController
{
    public function __construct(
        private ChemicalRepository $chemicalRepository,
    ) {

    }

    #[Route("/compounds", name: "app_compounds")]
    public function compounds(): Response
    {
        $chemicals = $this->chemicalRepository->findBy([], orderBy: ["shortName" => "ASC"]);

        return $this->render("compounds.html.twig", [
            "chemicals" => $chemicals
        ]);
    }

    #[Route("/compounds/view/{compoundId}", name: "app_compound_view")]
    public function viewCompound($compoundId): Response
    {
        $chemical = $this->chemicalRepository->find($compoundId);

        if (!$chemical) {
            $this->addFlash("error", "Chemical {$compoundId} was not found.");
            return $this->redirect("app_compounds", status: Response::HTTP_NOT_FOUND);
        }

        return $this->render("compound_view.html.twig", [
            "chemical" => $chemical,
        ]);
    }
}