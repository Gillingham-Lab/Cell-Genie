<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Antibody;
use App\Repository\AntibodyRepository;
use Doctrine\DBAL\Types\ConversionException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
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
    #[Route("/antibodies/{antibodyType}", name: "app_antibodies")]
    public function cells(Request $request, ?string $antibodyType = null): Response
    {
        $primaryAntibodies = [];
        $secondaryAntibodies = [];

        if ($antibodyType !== "primaries" and $antibodyType !== "secondaries" and !empty($antibodyType)) {
            throw new FileNotFoundException("The requested antibody type does not exist.");
        }

        if (!$antibodyType or $antibodyType === "primaries") {
            $primaryAntibodies = $this->antibodyRepository->findPrimaryAntibodies();
        }

        if (!$antibodyType or $antibodyType === "secondaries") {
            $secondaryAntibodies = $this->antibodyRepository->findSecondaryAntibodies(true);
        }

        return $this->render('parts/antibodies/antibodies.html.twig', [
            "antibodies" => [],
            "primaryAntibodies" => $primaryAntibodies,
            "secondaryAntibodies" => $secondaryAntibodies,
        ]);
    }

    #[Route("/antibodies/view/id/{antibodyId}", name: "app_antibody_view")]
    #[Route("/antibodies/view/{antibodyNr}", name: "app_antibody_view_number")]
    public function viewAntibody(Antibody $antibodyId = null, string $antibodyNr = null): Response
    {
        if ($antibodyId === null and $antibodyNr === null) {
            throw new FileNotFoundException();
        }

        if ($antibodyNr !== null) {
            $antibody = $this->antibodyRepository->findOneBy(["number" => $antibodyNr]);

            if (!$antibody) {
                $this->addFlash("error", "Antibody was not found");
                return $this->redirectToRoute("app_antibodies");
            }
        } else {
            $antibody = $antibodyId;
        }

        return $this->render("parts/antibodies/antibody.html.twig", [
            "antibody" => $antibody,
        ]);
    }

    #[Route("/antibodies/search", name: "app_antibodies_search", priority: 10)]
    public function search(Request $request): Response
    {
        $searchTerm = $request->request->get("search", null);

        if (!$searchTerm) {
            $this->addFlash("error", "Search term was empty.");
            return $this->redirectToRoute("app_antibodies");
        } elseif (strlen($searchTerm) < 3) {
            $this->addFlash("error", "Search term must contain at least 3 characters");
            return $this->redirectToRoute("app_antibodies");
        }

        $results =  $this->antibodyRepository->findBySearchTerm($searchTerm);

        if (empty($results)) {
            $this->addFlash("info", "No results found.");
            return $this->redirectToRoute("app_antibodies");
        }

        return $this->render('parts/antibodies/antibodies.html.twig', [
            "antibodies" => $results,
            "primaryAntibodies" => [],
            "secondaryAntibodies" => [],
        ]);
    }
}