<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\AnnotateableInterface;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Epitope;
use App\Entity\Lot;
use App\Form\Import\ImportOligoType;
use App\Form\Substance\AntibodyType;
use App\Form\Substance\ChemicalType;
use App\Form\Substance\EpitopeType;
use App\Form\Substance\LotType;
use App\Form\Substance\OligoType;
use App\Form\Substance\PlasmidType;
use App\Form\Substance\ProteinType;
use App\Genie\Enums\AntibodyType as AntibodyTypeEnum;
use App\Genie\Enums\PrivacyLevel;
use App\Repository\Cell\CellRepository;
use App\Repository\EpitopeRepository;
use App\Repository\LotRepository;
use App\Repository\Substance\AntibodyRepository;
use App\Repository\Substance\ChemicalRepository;
use App\Repository\Substance\OligoRepository;
use App\Repository\Substance\PlasmidRepository;
use App\Repository\Substance\ProteinRepository;
use App\Repository\Substance\SubstanceRepository;
use App\Security\Voter\Substance\LotVoter;
use App\Security\Voter\Substance\SubstanceVoter;
use App\Service\FileUploader;
use App\Service\GeneBankImporter;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class SubstanceController extends AbstractController
{
    private ?User $user;

    public function __construct(
        private Security $security
    ) {
        if ($this->security->getUser() instanceof User) {
            $this->user = $this->security->getUser();
        }
    }

    #[Route("/substance/view/{substance}", name: "app_substance_view")]
    public function viewSubstance(
        Substance $substance
    ): Response {
        $this->denyAccessUnlessGranted("view", $substance);

        return match($substance::class) {
            Antibody::class => $this->redirectToRoute("app_antibody_view", ["antibodyId" => $substance->getUlid()]),
            Chemical::class => $this->redirectToRoute("app_compound_view", ["compoundId" => $substance->getUlid()]),
            Oligo::class => $this->redirectToRoute("app_oligo_view", ["oligoId" => $substance->getUlid()]),
            Protein::class => $this->redirectToRoute("app_protein_view", ["proteinId" => $substance->getUlid()]),
            Plasmid::class => $this->redirectToRoute("app_plasmid_view", ["plasmidId" => $substance->getUlid()]),
            default => $this->createNotFoundException(),
        };
    }

    #[Route("/substance/lot/{lot}", name: "app_substance_lot_view")]
    public function viewLot(
        SubstanceRepository $substanceRepository,
        Lot $lot
    ): Response {
        $this->denyAccessUnlessGranted("view", $lot);
        $substance = $substanceRepository->findOneByLot($lot);

        if ($substance === null) {
            throw $this->createNotFoundException("A lot with the ID Number '{$lot->getNumber()}' and the id '{$lot->getId()}' was not found.");
        }

        $this->denyAccessUnlessGranted("view", $substance);

        // TODO: Show a real 'lot' information page.
        return $this->redirectToRoute("app_substance_view", ["substance" => $substance->getUlid()]);
    }

    #[Route("/substance/new/{type}", name: "app_substance_new")]
    #[Route("/substance/edit/{substance}", name: "app_substance_edit")]
    public function addSubstance(
        Request $request,
        SubstanceRepository $substanceRepository,
        GeneBankImporter $geneBankImporter,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        Substance $substance = null,
        string $type = null,
    ): Response {
        $new = !$substance;

        if ($type === null and $substance === null) {
            throw $this->createNotFoundException();
        }

        if ($new) {
            $this->denyAccessUnlessGranted("new", "Substance");

            $substance = match($type) {
                "antibody" => new Antibody(),
                "chemical" => new Chemical(),
                "oligo" => new Oligo(),
                "protein" => new Protein(),
                "plasmid" => new Plasmid(),
                default => null,
            };

            $substance->setOwner($this->user);
            $substance->setGroup($this->user?->getGroup());
        } else {
            $this->denyAccessUnlessGranted("edit", $substance);
        }

        if ($substance === null) {
            throw $this->createNotFoundException("Substance type '{$type}' has not been found.");
        }

        [$formType, $typeName, $overviewRoute, $specificRoute, $routeParam] = match($substance::class) {
            Antibody::class => [AntibodyType::class, "Antibody", "app_antibodies", "app_antibody_view", "antibodyId"],
            Chemical::class => [ChemicalType::class, "Chemical", "app_compounds", "app_compound_view", "compoundId"],
            Oligo::class => [OligoType::class, "Oligo", "app_oligos", "app_oligo_view", "oligoId"],
            Protein::class => [ProteinType::class, "Protein", "app_proteins", "app_protein_view", "proteinId"],
            Plasmid::class => [PlasmidType::class, "Plasmid", "app_plasmids", "app_plasmid_view", "plasmidId"],
            default => [null, null, null, null, null],
        };

        if ($formType === null) {
            throw $this->createNotFoundException("Substance form not found.");
        }

        $formOptions = [
            "save_button" => true,
        ];

        $form = $this->createForm($formType, $substance, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $fileUploader->upload($form);
            $fileUploader->updateFileSequence($substance);

            // If the substance is Annotateable, we call the GenBankImporter to help us import the file.
            if ($substance instanceof AnnotateableInterface) {
                try {
                    $imported = $geneBankImporter->addSequenceAnnotations(
                        $substance,
                        $substance->getAttachments(),
                        $form["_attachments"]["importSequence"]->getData(),
                        $form["_attachments"]["importFeatures"]->getData(),
                    );

                    if ($imported) {
                        $this->addFlash("success", "GenBank files have successfully been imported.");
                    }
                } catch (\Exception $e) {
                    $this->addFlash("error", "GenBank import was not successful: {$e->getMessage()}.");
                }
            }

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
                var_dump($e);
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
            "typeName" => $typeName,
        ]);
    }

    #[Route("/substance/{substance}/lot/add", name: "app_substance_add_lot")]
    #[Route("/substance/{substance}/lot/edit/{lot}", name: "app_substance_edit_lot")]
    public function addLotToSubstance(
        Request $request,
        SecurityController $securityController,
        EntityManagerInterface $entityManager,
        LotRepository $lotRepository,
        FileUploader $fileUploader,
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
            $this->denyAccessUnlessGranted(SubstanceVoter::ADD_LOT, $substance);

            $lot = new Lot();
            $lot->setBoughtBy($this->user);
            $lot->setOwner($this->user);
            $lot->setGroup($this->user?->getGroup());
        } else {
            $this->denyAccessUnlessGranted(LotVoter::EDIT, $lot);
        }

        $formOptions = [
            "save_button" => true,
            "hideVendor" => $substanceType === "Antibody",
        ];

        $form = $this->createForm(LotType::class, $lot, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $fileUploader->upload($form);
            $fileUploader->updateFileSequence($lot);

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
            "typeName" => $substanceType,
            "createLot" => true,
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
            $numberOfLots = $row[1];
            $numberOfAvailableLots = $row[2];

            if ($numberOfAvailableLots > 0) {
                $antibody->setAvailable(true);
            } else {
                $antibody->setAvailable(false);
            }

            $addPrimary = false;
            $addSecondary = false;

            if ($antibody->getType() === AntibodyTypeEnum::Primary) {
                $addPrimary = true;
            } else {
                $addSecondary = true;
            }

            if ($addPrimary and ($antibodyType === "primaries" or empty($antibodyType))) {
                $primaryAntibodies[] = $antibody;
            }

            if ($addSecondary and ($antibodyType === "secondaries" or empty($antibodyType))) {
                $secondaryAntibodies[] = $antibody;
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
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
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

        $this->denyAccessUnlessGranted("view", $antibody);

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
        $antibodies = [];

        if (empty($results)) {
            $this->addFlash("info", "No results found.");
            return $this->redirectToRoute("app_antibodies");
        }

        foreach ($results as $row) {
            /** @var Antibody $antibody */
            $antibody = $row[0];
            $numberOfLots = $row[1];
            $numberOfAvailableLots = $row[2];

            if ($numberOfAvailableLots > 0) {
                $antibody->setAvailable(true);
            } else {
                $antibody->setAvailable(false);
            }

            $antibodies[] = $antibody;
        }

        return $this->render('parts/antibodies/antibodies.html.twig', [
            "antibodies" => $antibodies,
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
        $this->denyAccessUnlessGranted("view", $chemical);

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
        $this->denyAccessUnlessGranted("view", $oligo);

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
        $this->denyAccessUnlessGranted("view", $protein);

        $associatedCells = $cellRepository->fetchByProtein($protein);

        return $this->render("parts/proteins/protein.html.twig", [
            "protein" => $protein,
            "associatedCells" => $associatedCells,
        ]);
    }

    #[Route("/plasmid", name: "app_plasmids")]
    public function viewPlasmids(
        PlasmidRepository $plasmidRepository,
    ): Response {
        $plasmids = $plasmidRepository->findAllWithLotCount();

        return $this->render("parts/plasmids/plasmids.html.twig", [
            "plasmids" => $plasmids,
        ]);
    }

    #[Route("/plasmid/view/{plasmidId}", name: "app_plasmid_view")]
    #[ParamConverter("plasmid", options: ["mapping" => ["plasmidId"  => "ulid"]])]
    public function viewPlasmid(
        Plasmid $plasmid
    ): Response {
        $this->denyAccessUnlessGranted("view", $plasmid);

        return $this->render("parts/plasmids/plasmid.html.twig", [
            "plasmid" => $plasmid,
        ]);
    }

    #[Route("/epitope", name: "app_epitopes")]
    public function epitopes(
        EpitopeRepository $epitopeRepository,
    ): Response {
        $epitopes = $epitopeRepository->findAll();

        return $this->render("parts/epitopes/epitopes.html.twig", [
            "epitopes" => $epitopes,
        ]);
    }

    #[Route("/epitope/view/{epitope}", name: "app_epitope_view")]
    public function viewEpitope(
        Epitope $epitope,
    ): Response {
       return $this->render("parts/epitopes/epitope.html.twig", [
           "epitope" => $epitope,
       ]);
    }

    #[Route("/epitope/new", name: "app_epitope_new")]
    #[Route("/epitope/edit/{epitope}", name: "app_epitope_edit")]
    public function addEpitope(
        Request $request,
        EpitopeRepository $epitopeRepository,
        EntityManagerInterface $entityManager,
        Epitope $epitope = null,
    ): Response {
        $new = !$epitope;

        if ($new) {
            $epitope = new Epitope();
        }

        $formType = EpitopeType::class;

        $formOptions = [
            "save_button" => true,
        ];

        $form = $this->createForm($formType, $epitope, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            try {
                if ($new) {
                    $entityManager->persist($epitope);
                    $message = "The new epitope was successfully created.";
                } else {
                    $message = "The epitope '{$epitope->getShortName()}' was successfully changed.";
                }

                $entityManager->flush();
                $this->addFlash("success", $message);

                return $this->redirectToRoute("app_epitope_view", ["epitope" => $epitope->getId()]);
            } catch (\Exception $e) {
                if ($new) {
                    $this->addFlash("error", "Adding a new epitope was not possible. Reason: {$e->getMessage()}.");
                } else {
                    $this->addFlash("error", "Changing the epitope '{$epitope->getShortName()}' was not possible. Reason: {$e->getMessage()}.");
                }
            }
        }

        return $this->render("parts/forms/add_substance.html.twig", [
            "title" => $new ? "Epitope :: New" : "Epitope :: {$epitope->getShortName()} :: Edit",
            "substance" => ($new ? null : $epitope),
            "form" => $form,
            "returnTo" => $new ? $this->generateUrl("app_epitopes") : $this->generateUrl("app_epitope_view", ["epitope" => $epitope->getId()]),
        ]);
    }

    #[Route("/substance/import/{type}", name: "app_substance_import")]
    #[IsGranted("ROLE_USER")]
    public function import(
        Security $security,
        string $type,
    ) {
        /** @var User $user */
        $user = $security->getUser();

        $data = [
            "substance" => [
                "owner" => $user,
                "group" => $user->getGroup(),
                "privacyLevel" => PrivacyLevel::Group,
            ]
        ];
        $builder = $this->createFormBuilder($data);

        $builder->add("substance", ImportOligoType::class, [

        ]);

        $form = $builder->getForm();


        return $this->render("parts/substance/import.html.twig", [
            "importForm" => $form,
        ]);
    }
}