<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Antibody;
use App\Entity\Epitope;
use App\Entity\EpitopeHost;
use App\Entity\EpitopeProtein;
use App\Entity\EpitopeSmallMolecule;
use App\Repository\AntibodyRepository;
use Doctrine\DBAL\Types\ConversionException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
    #[Route("/antibodies/epitope/{epitope}", name: "app_antibodies_epitope")]
    public function cells(
        Request $request,
        ?string $antibodyType = null,
        ?Epitope $epitope = null,
    ): Response {
        $primaryAntibodies = [];
        $secondaryAntibodies = [];

        if (!empty($antibodyType) and !in_array($antibodyType, ["primaries", "secondaries"])) {
            throw new FileNotFoundException("The requested antibody type does not exist.");
        }

        $antibodies = $this->antibodyRepository->findAnyAntibody($epitope);
        $primaryAntibodies = [];
        $secondaryAntibodies = [];

        if ($epitope !== null) {
            return $this->render('parts/antibodies/antibodies.html.twig', [
                "antibodies" => $antibodies,
            ]);
        }

        /** @var Antibody $antibody */
        foreach ($antibodies as $row) {
            $antibody = $row[0];

            $addPrimary = false;
            $addSecondary = false;

            $epitopes = $antibody->getEpitopeTargets();

            foreach ($epitopes as $epitope) {
                if ($epitope instanceof EpitopeHost) {
                    $addSecondary = true;
                }

                if ($epitope instanceof EpitopeProtein or $epitope instanceof EpitopeSmallMolecule) {
                    $addPrimary = true;
                }
            }

            if ($addPrimary and ($antibodyType === "primaries" or empty($antibodyType))) {
                $primaryAntibodies[] = $row;
            }

            if ($addSecondary and ($antibodyType === "secondaries" or empty($antibodyType))) {
                $secondaryAntibodies[] = $row;
            }
        }

        return $this->render('parts/antibodies/antibodies.html.twig', [
            "antibodies" => [],
            "primaryAntibodies" => $primaryAntibodies,
            "secondaryAntibodies" => $secondaryAntibodies,
        ]);
    }

    #[Route("/antibodies/view/id/{antibodyId}", name: "app_antibody_view")]
    #[ParamConverter("antibodyId", options: ["mapping" => ["antibodyId"  => "ulid"]])]
    #[Route("/antibodies/view/{antibodyNr}", name: "app_antibody_view_number")]
    public function viewAntibody(Antibody $antibodyId = null, string $antibodyNr = null): Response
    {
        if ($antibodyId === null and $antibodyNr === null) {
            throw new FileNotFoundException("Antibody not found.");
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