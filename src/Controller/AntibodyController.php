<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\AntibodyRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AntibodyController extends AbstractController
{
    public function __construct(
        private AntibodyRepository $antibodyRepository,
    ) {
    }

    #[Route("/antibodies", name: "app_antibodies")]
    public function cells(): Response
    {
        $primaryAntibodies = $this->antibodyRepository->findPrimaryAntibodies();
        $secondaryAntibodies = $this->antibodyRepository->findSecondaryAntibodies(true);

        return $this->render('parts/antibodies/antibodies.html.twig', [
            "primaryAntibodies" => $primaryAntibodies,
            "secondaryAntibodies" => $secondaryAntibodies,
        ]);
    }
}