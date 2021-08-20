<?php
declare(strict_types=1);

namespace App\Controller;

use App\Repository\AntibodyRepository;
use Doctrine\DBAL\Types\ConversionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route("/antibodies/view/{antibodyId}", name: "app_antibody_view")]
    public function viewAntibody(string $antibodyId): Response
    {
        try {
            $antibody = $this->antibodyRepository->find($antibodyId);
        } catch (ConversionException) {
            $antibody = null;
        }

        # Return if antibody was not found.
        if (!$antibody) {
            $this->addFlash("error", "Antibody was not found");
            return $this->redirectToRoute("app_antibodies");
        }

        return $this->render("parts/antibodies/antibody.html.twig", [
            "antibody" => $antibody,
        ]);
    }

    #[Route("/antibodies/search", name: "app_antibodies_search")]
    public function search(Request $request): Response
    {
        return $this->render('parts/antibodies/antibodies.html.twig', [
            "primaryAntibodies" => [],
            "secondaryAntibodies" => [],
        ]);
    }
}