<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\ChemicalRepository;
use App\Repository\ProteinRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProteinController extends AbstractController
{
    public function __construct(
        private ProteinRepository $proteinRepository,
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
}