<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Chemical;
use App\Repository\ChemicalRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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

        return $this->render("parts/compounds/compounds.html.twig", [
            "chemicals" => $chemicals
        ]);
    }

    #[Route("/compounds/view/{compoundId}", name: "app_compound_view")]
    #[ParamConverter("chemical", options: ["mapping" => ["compoundId" => "ulid"]])]
    public function viewCompound(Chemical $chemical): Response
    {
        return $this->render("parts/compounds/compound.html.twig", [
            "chemical" => $chemical,
        ]);
    }
}