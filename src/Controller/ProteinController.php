<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Epitope;
use App\Entity\Protein;
use App\Repository\ExperimentTypeRepository;
use App\Repository\ProteinRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
    #[Route("/protein/epitope/{epitope}", name: "app_proteins_epitope")]
    public function proteins(Epitope $epitope = null): Response
    {
        $proteins = $this->proteinRepository->findWithAntibodies($epitope, orderBy: ["p.shortName" => "ASC"]);

        return $this->render("parts/proteins/proteins.html.twig", [
            "proteins" => $proteins
        ]);
    }

    #[Route("/protein/view/{proteinId}", name: "app_protein_view")]
    #[ParamConverter("protein", options: ["mapping" => ["proteinId"  => "ulid"]])]
    public function viewProtein(Protein $protein): Response
    {
        return $this->render("parts/proteins/protein.html.twig", [
            "protein" => $protein,
        ]);
    }
}