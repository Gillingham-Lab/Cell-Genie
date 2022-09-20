<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\Epitope;
use App\Entity\EpitopeHost;
use App\Entity\EpitopeProtein;
use App\Entity\EpitopeSmallMolecule;
use App\Entity\Lot;
use App\Form\Substance\ChemicalType;
use App\Form\Substance\LotType;
use App\Form\Substance\OligoType;
use App\Form\Substance\ProteinType;
use App\Repository\Cell\CellRepository;
use App\Repository\LotRepository;
use App\Repository\Substance\AntibodyRepository;
use App\Repository\Substance\ChemicalRepository;
use App\Repository\Substance\OligoRepository;
use App\Repository\Substance\ProteinRepository;
use App\Repository\Substance\SubstanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubstanceController extends AbstractController
{

    #[Route("/substance/view/{substance}", name: "app_substance_view")]
    public function viewSubstance(
        Substance $substance
    ) {
        return match($substance::class) {
            Antibody::class => $this->redirectToRoute("app_antibody_view", ["antibodyId" => $substance->getUlid()]),
            Chemical::class => $this->redirectToRoute("app_compound_view", ["compoundId" => $substance->getUlid()]),
            Oligo::class => $this->redirectToRoute("app_oligo_view", ["oligoId" => $substance->getUlid()]),
            Protein::class => $this->redirectToRoute("app_protein_view", ["proteinId" => $substance->getUlid()]),
            default => $this->createNotFoundException(),
        };
    }

    #[Route("/substance/new/{type}", name: "app_substance_new")]
    #[Route("/substance/edit/{substance}", name: "app_substance_edit")]
    public function addSubstance(
        Request $request,
        SubstanceRepository $substanceRepository,
        EntityManagerInterface $entityManager,
        Substance $substance = null,
        string $type = null,
    ): Response {
        $new = !$substance;

        if ($type === null and $substance === null) {
            $this->createNotFoundException();
        }

        if ($new) {
            $substance = match($type) {
                "antibody" => new Antibody(),
                "chemical" => new Chemical(),
                "oligo" => new Oligo(),
                "protein" => new Protein(),
            };
        }

        [$formType, $typeName, $overviewRoute, $specificRoute, $routeParam] = match($substance::class) {
            Antibody::class => [null, "Antibody", "app_antibodies", "app_antibody_view", "antibodyId"],
            Chemical::class => [ChemicalType::class, "Chemical", "app_compounds", "app_compound_view", "compoundId"],
            Oligo::class => [OligoType::class, "Oligo", "app_oligos", "app_oligo_view", "oligoId"],
            Protein::class => [ProteinType::class, "Protein", "app_proteins", "app_protein_view", "proteinId"],
            default => [$this->createNotFoundException(), null, null, null, null],
        };

        $formOptions = [
            "save_button" => true,
        ];

        $form = $this->createForm($formType, $substance, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            try {
                if ($new) {
                    $entityManager->persist($substance);
                    $message = "The new {$typeName} was successfully created.";
                } else {
                    $message = "The {$typeName} {$substance->getShortName()} was successfully changed.";
                }

                $entityManager->flush();
                $this->addFlash("success", $message);

                return $this->redirectToRoute("app_substance_view", ["substance" => $substance->getUlid()]);
            } catch (\Exception $e) {
                if ($new) {
                    $this->addFlash("error", "Adding a new {$typeName} was not possible. Reason: {$e->getMessage()}.");
                } else {
                    $this->addFlash("error", "Changing the {$typeName} {$substance->getShortName()} was not possible. Reason: {$e->getMessage()}.");
                }
            }
        }

        return $this->renderForm("parts/forms/add_substance.html.twig", [
            "title" => $new ? "{$typeName} :: New" : "{$typeName} :: {$substance->getShortName()} :: Edit",
            "substance" => ($new ? null : $substance),
            "form" => $form,
            "returnTo" => $new ? $this->generateUrl($overviewRoute) : $this->generateUrl($specificRoute, [$routeParam => $substance->getUlid()]),
        ]);
    }

    #[Route("/substance/{substance}/lot/add", name: "app_substance_add_lot")]
    #[Route("/substance/{substance}/lot/edit/{lot}", name: "app_substance_edit_lot")]
    public function addLotToSubstance(
        Request $request,
        SecurityController $securityController,
        EntityManagerInterface $entityManager,
        LotRepository $lotRepository,
        Substance $substance ,
        Lot $lot = null,
    ): Response {
        $new = !$lot;
        $substanceType = match ($substance::class) {
            Antibody::class => "Antibody",
            Chemical::class => "Chemical",
            Oligo::class => "Oligo",
            Protein::class => "Protein",
            default => "Other",
        };

        if (!$lot) {
            $lot = new Lot();
            $lot->setBoughtBy($securityController->getUser());
        }

        $formOptions = [
            "save_button" => true,
            "hideVendor" => $substanceType === "Antibody",
        ];

        $form = $this->createForm(LotType::class, $lot, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            try {
                if ($new) {
                    $substance->addLot($lot);
                    $message = "The new lot was successfully created.";
                } else {
                    $message = "The lot {$substance->getShortName()}.{$lot->getNumber()} was successfully changed.";
                }

                $entityManager->flush();
                $this->addFlash("success", $message);

                return $this->redirectToRoute("app_substance_view", ["substance" => $substance->getUlid()]);
            } catch (\Exception $e) {
                if ($new) {
                    $this->addFlash("error", "Adding a new lot was not possible. Reason: {$e->getMessage()}.");
                } else {
                    $this->addFlash("error", "Changing the lot {$substance->getShortName()}.{$lot->getNumber()} was not possible. Reason: {$e->getMessage()}.");
                }
            }
        }

        return $this->renderForm("parts/forms/add_substance.html.twig", [
            "substance_type" => $substanceType,
            "title" => $new ? "$substanceType :: Lot :: New" : "$substanceType :: {$substance->getShortName()} :: Lot :: {$lot->getNumber()} :: Edit",
            "substance" => ($new ? null : $substance),
            "form" => $form,
            "returnTo" => $this->generateUrl("app_substance_view", ["substance" => $substance->getUlid()]),
        ]);
    }

    #[Route("/antibodies", name: "app_antibodies")]
    #[Route("/antibodies/{antibodyType}", name: "app_antibodies")]
    #[Route("/antibodies/epitope/{epitope}", name: "app_antibodies_epitope")]
    public function viewAntibodies(
        Request $request,
        AntibodyRepository $antibodyRepository,
        ?string $antibodyType = null,
        ?Epitope $epitope = null,
    ): Response {
        $primaryAntibodies = [];
        $secondaryAntibodies = [];

        if (!empty($antibodyType) and !in_array($antibodyType, ["primaries", "secondaries"])) {
            throw new FileNotFoundException("The requested antibody type does not exist.");
        }

        $antibodies = $antibodyRepository->findAnyAntibody($epitope);

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
    public function viewAntibody(
        AntibodyRepository $antibodyRepository,
        Antibody $antibodyId = null,
        string $antibodyNr = null
    ): Response {
        if ($antibodyId === null and $antibodyNr === null) {
            throw new FileNotFoundException("Antibody not found.");
        }

        if ($antibodyNr !== null) {
            $antibody = $antibodyRepository->findOneBy(["number" => $antibodyNr]);

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
    public function searchAntibodies(
        AntibodyRepository $antibodyRepository,
        Request $request
    ): Response {
        $searchTerm = $request->request->get("search", null);

        if (!$searchTerm) {
            $this->addFlash("error", "Search term was empty.");
            return $this->redirectToRoute("app_antibodies");
        } elseif (strlen($searchTerm) < 3) {
            $this->addFlash("error", "Search term must contain at least 3 characters");
            return $this->redirectToRoute("app_antibodies");
        }

        $results =  $antibodyRepository->findBySearchTerm($searchTerm);

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

    #[Route("/compounds", name: "app_compounds")]
    public function compounds(
        ChemicalRepository $chemicalRepository
    ): Response {
        $chemicals = $chemicalRepository->findBy([], orderBy: ["shortName" => "ASC"]);

        return $this->render("parts/compounds/compounds.html.twig", [
            "chemicals" => $chemicals
        ]);
    }

    #[Route("/compounds/view/{compoundId}", name: "app_compound_view")]
    #[ParamConverter("chemical", options: ["mapping" => ["compoundId" => "ulid"]])]
    public function viewCompound(
        Chemical $chemical
    ): Response {
        return $this->render("parts/compounds/compound.html.twig", [
            "chemical" => $chemical,
        ]);
    }

    #[Route("/oligos", name: "app_oligos")]
    public function oligos(
        OligoRepository $oligoRepository,
    ): Response {
        $oligos = $oligoRepository->findAllWithLotCount();

        return $this->render("parts/oligos/oligos.html.twig", [
            "oligos" => $oligos,
        ]);
    }

    #[Route("/oligos/view/{oligoId}", name: "app_oligo_view")]
    #[ParamConverter("oligo", options: ["mapping" => ["oligoId"  => "ulid"]])]
    public function viewOligo(
        Oligo $oligo,
    ): Response {
        return $this->render("parts/oligos/oligo.html.twig", [
            "oligo" => $oligo,
        ]);
    }

    #[Route("/protein", name: "app_proteins")]
    #[Route("/protein/epitope/{epitope}", name: "app_proteins_epitope")]
    public function proteins(
        ProteinRepository $proteinRepository,
        Epitope $epitope = null
    ): Response {
        $proteins = $proteinRepository->findWithAntibodiesAndLotCount($epitope, orderBy: ["p.shortName" => "ASC"]);

        return $this->render("parts/proteins/proteins.html.twig", [
            "proteins" => $proteins
        ]);
    }

    #[Route("/protein/view/{proteinId}", name: "app_protein_view")]
    #[ParamConverter("protein", options: ["mapping" => ["proteinId"  => "ulid"]])]
    public function viewProtein(
        CellRepository $cellRepository,
        Protein $protein
    ): Response {
        $associatedCells = $cellRepository->fetchByProtein($protein);

        return $this->render("parts/proteins/protein.html.twig", [
            "protein" => $protein,
            "associatedCells" => $associatedCells,
        ]);
    }
}