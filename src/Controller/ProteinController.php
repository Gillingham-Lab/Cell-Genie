<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\ChemicalRepository;
use App\Repository\ExperimentTypeRepository;
use App\Repository\ProteinRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProteinController extends AbstractController
{
    public function __construct(
        private ProteinRepository $proteinRepository,
        private ExperimentTypeRepository $experimentTypeRepository,
    ) {

    }

    #[Route("/protein", name: "app_proteins")]
    public function proteins(): Response
    {
        $proteins = $this->proteinRepository->findBy([], orderBy: ["shortName" => "ASC"]);

        return $this->render("proteins.html.twig", [
            "proteins" => $proteins
        ]);
    }

    #[Route("/protein/view/{proteinId<\d+>}", name: "app_protein_view")]
    public function viewProtein($proteinId): Response
    {
        $protein = $this->proteinRepository->find($proteinId);

        if (!$protein) {
            $this->createNotFoundException();
        }

        # Get all experiment types used for this protein
        $experimentTypes = $this->experimentTypeRepository->findByProtein($protein);

        # Try to get experiment types for each antibody of the current protein
        $antibodies = $protein->getAntibodies();
        $antibodyToExperimentType = [];

        foreach ($antibodies as $antibody) {
            $antibodyExperimentTypes = $this->experimentTypeRepository->findByAntibody($antibody);
            $antibodyToExperimentType[$antibody->getId()] = $antibodyExperimentTypes;
        }

        return $this->render("protein_view.html.twig", [
            "protein" => $protein,
            "experimentTypes" => $experimentTypes,
            "experimentTypesPerAntibody" => $antibodyToExperimentType,
        ]);
    }
}