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
}