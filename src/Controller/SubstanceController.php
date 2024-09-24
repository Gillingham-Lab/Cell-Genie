<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\AnnotateableInterface;
use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellProtein;
use App\Entity\DoctrineEntity\Substance\Antibody;
use App\Entity\DoctrineEntity\Substance\Chemical;
use App\Entity\DoctrineEntity\Substance\Oligo;
use App\Entity\DoctrineEntity\Substance\Plasmid;
use App\Entity\DoctrineEntity\Substance\Protein;
use App\Entity\DoctrineEntity\Substance\Substance;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Epitope;
use App\Entity\FormEntity\DetectionEntry;
use App\Entity\Lot;
use App\Entity\SequenceAnnotation;
use App\Entity\Table\ColorColumn;
use App\Entity\Table\Column;
use App\Entity\Table\ComponentColumn;
use App\Entity\Table\Table;
use App\Entity\Table\ToggleColumn;
use App\Entity\Table\ToolboxColumn;
use App\Entity\Toolbox\AddTool;
use App\Entity\Toolbox\ClipwareTool;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Tool;
use App\Entity\Toolbox\Toolbox;
use App\Entity\Toolbox\ViewTool;
use App\Form\Import\ImportLotType;
use App\Form\Import\ImportOligoType;
use App\Form\Substance\AntibodyType;
use App\Form\Substance\ChemicalType;
use App\Form\Substance\EpitopeType;
use App\Form\Substance\LotType;
use App\Form\Substance\OligoType;
use App\Form\Substance\PlasmidType;
use App\Form\Substance\ProteinType;
use App\Genie\Enums\PrivacyLevel;
use App\Repository\Cell\CellRepository;
use App\Repository\EpitopeRepository;
use App\Repository\LotRepository;
use App\Repository\Storage\BoxRepository;
use App\Repository\Substance\OligoRepository;
use App\Repository\Substance\SubstanceRepository;
use App\Repository\User\UserGroupRepository;
use App\Repository\User\UserRepository;
use App\Security\Voter\Substance\LotVoter;
use App\Security\Voter\Substance\SubstanceVoter;
use App\Service\FileUploader;
use App\Service\GeneBankImporter;
use App\Service\IconService;
use App\Twig\Components\EntityReference;
use App\Twig\Components\Metadata;
use Doctrine\DBAL\Exception\ServerException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
    public function addSubstance(
        Request $request,
        GeneBankImporter $geneBankImporter,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        string $type,
    ): Response {
        return $this->addOrEditSubstance(
            $request, $geneBankImporter, $entityManager, $fileUploader, null, $type,
        );
    }

    #[Route("/substance/edit/{substance}", name: "app_substance_edit")]
    #[IsGranted("edit", "substance")]
    public function editSubstance(
        Request $request,
        GeneBankImporter $geneBankImporter,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        Substance $substance = null,
    ): Response {
        return $this->addOrEditSubstance(
            $request, $geneBankImporter, $entityManager, $fileUploader, $substance, null,
        );
    }

    private function addOrEditSubstance(
        Request $request,
        GeneBankImporter $geneBankImporter,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        ?Substance $substance = null,
        ?string $type = null,
    ): Response {
        $new = is_null($substance);

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

        [$formType, $typeName, $overviewRoute, $specificRoute, $routeParam, $icon] = match($substance::class) {
            Antibody::class => [AntibodyType::class, "Antibody", "app_antibodies", "app_antibody_view", "antibodyId", "antibody"],
            Chemical::class => [ChemicalType::class, "Chemical", "app_compounds", "app_compound_view", "compoundId", "compound"],
            Oligo::class => [OligoType::class, "Oligo", "app_oligos", "app_oligo_view", "oligoId", "oligo"],
            Protein::class => [ProteinType::class, "Protein", "app_proteins", "app_protein_view", "proteinId", "protein"],
            Plasmid::class => [PlasmidType::class, "Plasmid", "app_plasmids", "app_plasmid_view", "plasmidId", "plasmid"],
            default => [null, null, null, null, null, null],
        };

        if ($formType === null) {
            throw $this->createNotFoundException("Substance form not found.");
        }

        $formOptions = [
            "save_button" => true,
            "show_lots" => $new,
            "hide_lot_vendor" => $typeName === "Antibody",
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
                if ($new) {
                    $this->addFlash("error", "Adding a new {$typeName} was not possible. Reason: {$e->getMessage()}.");
                } else {
                    $this->addFlash("error", "Changing the {$typeName} {$substance->getShortName()} was not possible. Reason: {$e->getMessage()}.");
                }
            }
        }

        return $this->render("parts/forms/add_substance.html.twig", [
            "title" => $new ? "{$typeName} :: New" : "{$typeName} :: {$substance->getShortName()} :: Edit",
            "substance" => ($new ? null : $substance),
            "form" => $form,
            "returnTo" => $new ? $this->generateUrl($overviewRoute) : $this->generateUrl($specificRoute, [$routeParam => $substance->getUlid()]),
            "typeName" => $typeName,
            "icon" => $icon
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

        return $this->render("parts/forms/add_substance.html.twig", [
            "substance_type" => $substanceType,
            "title" => $new ? "$substanceType :: Lot :: New" : "$substanceType :: {$substance->getShortName()} :: Lot :: {$lot->getNumber()} :: Edit",
            "substance" => ($new ? null : $substance),
            "form" => $form,
            "returnTo" => $this->generateUrl("app_substance_view", ["substance" => $substance->getUlid()]),
            "typeName" => $substanceType,
            "createLot" => true,
            "icon" => "lot",
        ]);
    }

    #[Route("/antibodies", name: "app_antibodies")]
    public function viewAntibodies(
        IconService $iconService,
    ): Response {
        return $this->render("parts/substance/search.html.twig", [
            "title" => "Antibodies",
            "icon" => "antibody",
            "substanceType" => "antibody",
        ]);
    }

    #[Route("/antibodies/view/id/{antibodyId}", name: "app_antibody_view")]
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
    #[IsGranted("view", "antibody")]
    public function viewAntibody(
        #[MapEntity(mapping: ["antibodyId"  => "ulid"])]
        Antibody $antibody,
    ) {
        return $this->render("parts/substance/view_antibody.html.twig", [
            "title" => "{$antibody->getNumber()} - {$antibody->getShortName()}",
            "subtitle" => $antibody->getCitation(),
            "antibody" => $antibody,
            "toolbox" => new Toolbox([
                new Tool(
                    path: $this->generateUrl("app_antibodies"),
                    icon: "antibody",
                    buttonClass: "btn-secondary",
                    tooltip: "Seach antibody",
                    iconStack: "search",
                ),
                new ClipwareTool(
                    clipboardText: $antibody->getCitation(),
                    tooltip: "Copy citation on antibody",
                ),
                new EditTool(
                    path: $this->generateUrl("app_substance_edit", ["substance" => $antibody->getUlid()]),
                    icon: "antibody",
                    tooltip: "Edit antibody",
                    iconStack: "edit",
                ),
                new AddTool(
                    path: $this->generateUrl("app_substance_add_lot", ["substance" => $antibody->getUlid()]),
                    icon: "lot",
                    tooltip: "Register a new lot",
                    iconStack: "add",
                )
            ])
        ]);
    }

    #[Route("/antibodies/view/{antibodyNr}", name: "app_antibody_view_number")]
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
    #[IsGranted("view", "antibody")]
    public function viewAntibodyByNumber(
        #[MapEntity(mapping: ["antibodyNr" => "number"])]
        Antibody $antibody,
    ) {
        return $this->viewAntibody($antibody);
    }

    #[Route("/compounds", name: "app_compounds")]
    #[IsGranted("ROLE_USER")]
    public function compounds(): Response
    {
        return $this->render("parts/substance/search.html.twig", [
            "title" => "Chemicals",
            "icon" => "chemical",
            "substanceType" => "chemical",
        ]);
    }

    #[Route("/compounds/view/{compoundId}", name: "app_compound_view")]
    #[IsGranted("view", "chemical")]
    public function viewCompound(
        #[MapEntity(mapping: ["compoundId" => "ulid"])]
        Chemical $chemical
    ): Response {
        return $this->render("parts/substance/view_compound.html.twig", [
            "title" => $chemical->getShortName(),
            "subtitle" => $chemical->getCitation(),
            "chemical" => $chemical,
            "toolbox" => new Toolbox([
                new Tool(
                    path: $this->generateUrl("app_compounds"),
                    icon: "compound",
                    buttonClass: "btn-secondary",
                    tooltip: "Seach chemical",
                    iconStack: "search",
                ),
                new ClipwareTool(
                    clipboardText: $chemical->getCitation(),
                    tooltip: "Copy citation",
                ),
                new EditTool(
                    path: $this->generateUrl("app_substance_edit", ["substance" => $chemical->getUlid()]),
                    icon: "compound",
                    tooltip: "Edit chemical",
                    iconStack: "edit",
                ),
                new AddTool(
                    path: $this->generateUrl("app_substance_add_lot", ["substance" => $chemical->getUlid()]),
                    icon: "lot",
                    tooltip: "Register a new lot",
                    iconStack: "add",
                )
            ]),
        ]);
    }

    #[Route("/oligos", name: "app_oligos")]
    public function oligos(
        OligoRepository $oligoRepository,
    ): Response {
        return $this->render("parts/substance/search.html.twig", [
            "title" => "Oligos",
            "icon" => "oligo",
            "substanceType" => "oligo",
        ]);
    }

    #[Route("/oligos/view/{oligoId}", name: "app_oligo_view")]
    #[IsGranted("view", "oligo")]
    public function viewOligo(
        #[MapEntity(mapping: ["oligoId" => "ulid"])]
        Oligo $oligo,
    ): Response {
        return $this->render("parts/substance/view_oligo.html.twig", [
            "title" => $oligo->getShortName(),
            "subtitle" => $oligo->getCitation(),
            "oligo" => $oligo,
            "toolbox" => new Toolbox([
                new Tool(
                    path: $this->generateUrl("app_oligos"),
                    icon: "oligo",
                    buttonClass: "btn-secondary",
                    tooltip: "Seach oligo",
                    iconStack: "search",
                ),
                new ClipwareTool(
                    clipboardText: $oligo->getCitation(),
                    tooltip: "Copy citation",
                ),
                new EditTool(
                    path: $this->generateUrl("app_substance_edit", ["substance" => $oligo->getUlid()]),
                    icon: "oligo",
                    tooltip: "Edit oligo",
                    iconStack: "edit",
                ),
                new AddTool(
                    path: $this->generateUrl("app_substance_add_lot", ["substance" => $oligo->getUlid()]),
                    icon: "lot",
                    tooltip: "Register a new lot",
                    iconStack: "add",
                )
            ]),
        ]);
    }

    #[Route("/plasmid", name: "app_plasmids")]
    public function viewPlasmids(): Response
    {
        return $this->render("parts/substance/search.html.twig", [
            "title" => "Plasmids",
            "icon" => "plasmid",
            "substanceType" => "plasmid",
        ]);
    }

    #[Route("/plasmid/view/{plasmidId}", name: "app_plasmid_view")]
    #[IsGranted("view", "plasmid")]
    public function plasmids(
        #[MapEntity(mapping: ["plasmidId" => "ulid"])]
        Plasmid $plasmid
    ): Response {
        return $this->render("parts/substance/view_plasmid.html.twig", [
            "title" => $plasmid->getShortName(),
            "subtitle" => $plasmid->getCitation(),
            "plasmid" => $plasmid,
            "toolbox" => new Toolbox([
                new Tool(
                    path: $this->generateUrl("app_plasmids"),
                    icon: "plasmid",
                    buttonClass: "btn-secondary",
                    tooltip: "Seach plasmid",
                    iconStack: "search",
                ),
                new ClipwareTool(
                    clipboardText: $plasmid->getCitation(),
                    tooltip: "Copy citation",
                ),
                new EditTool(
                    path: $this->generateUrl("app_substance_edit", ["substance" => $plasmid->getUlid()]),
                    icon: "plasmid",
                    tooltip: "Edit plasmid",
                    iconStack: "edit",
                ),
                new AddTool(
                    path: $this->generateUrl("app_substance_add_lot", ["substance" => $plasmid->getUlid()]),
                    icon: "lot",
                    tooltip: "Register a new lot",
                    iconStack: "add",
                )
            ]),
            "annotations" => new Table(
                data: $plasmid->getSequenceAnnotations(),
                columns: [
                    new Column("Name", fn (SequenceAnnotation $annotation) => $annotation->getAnnotationLabel()),
                    new Column("Type", fn (SequenceAnnotation $annotation) => $annotation->getAnnotationType()),
                    new Column("Spans", fn (SequenceAnnotation $annotation) => "{$annotation->getAnnotationStart()} .. {$annotation->getAnnotationEnd()}"),
                    new ToggleColumn("Forward", fn (SequenceAnnotation $annotation) => !$annotation->isComplement()),
                    new ColorColumn("Color", fn (SequenceAnnotation $annotation) => $annotation->getColor() ?? "grey"),
                ]
            )
        ]);
    }

    #[Route("/protein", name: "app_proteins")]
    public function proteins(
    ): Response {
        return $this->render("parts/substance/search.html.twig", [
            "title" => "Proteins",
            "icon" => "protein",
            "substanceType" => "protein",
        ]);
    }

    #[Route("/protein/view/{proteinId}", name: "app_protein_view")]
    #[IsGranted("view", "protein")]
    public function viewProtein(
        CellRepository $cellRepository,
        #[MapEntity(mapping: ["proteinId" => "ulid"])]
        Protein $protein
    ): Response {
        $antibodies = [];
        /** @var Epitope $epitope */
        foreach ($protein->getEpitopes() as $epitope) {
            /** @var Antibody $antibody */
            foreach ($epitope->getAntibodies() as $antibody) {
                $antibodies[$antibody->getNumber()] = $antibody;
            }
        }
        ksort($antibodies);

        $cells = $cellRepository->fetchByProtein($protein);

        return $this->render("parts/substance/view_protein.html.twig", [
            "title" => $protein->getShortName(),
            "subtitle" => $protein->getCitation(),
            "protein" => $protein,
            "toolbox" => new Toolbox([
                new Tool(
                    path: $this->generateUrl("app_proteins"),
                    icon: "protein",
                    buttonClass: "btn-secondary",
                    tooltip: "Seach protein",
                    iconStack: "search",
                ),
                new ClipwareTool(
                    clipboardText: $protein->getCitation(),
                    tooltip: "Copy citation",
                ),
                new EditTool(
                    path: $this->generateUrl("app_substance_edit", ["substance" => $protein->getUlid()]),
                    icon: "protein",
                    tooltip: "Edit protein",
                    iconStack: "edit",
                ),
                new AddTool(
                    path: $this->generateUrl("app_substance_add_lot", ["substance" => $protein->getUlid()]),
                    icon: "lot",
                    tooltip: "Register a new lot",
                    iconStack: "add",
                )
            ]),
            "antibodyTable" => new Table(
                data: $antibodies,
                columns: [
                    new ToolboxColumn("", fn (Antibody $antibody) => new Toolbox([
                        new ViewTool(
                            path: $this->generateUrl("app_antibody_view_number", ["antibodyNr" => $antibody->getNumber()]),
                            icon: "antibody",
                            iconStack: "view",
                        ),
                    ])),
                    new Column("Number", fn (Antibody $antibody) => $antibody->getNumber()),
                    new ComponentColumn("Lots", fn(Antibody $antibody) => [
                        EntityReference::class,
                        [
                            "entity" => $antibody->getAvailableLots(),
                        ]
                    ])
                ]
            ),
            "associatedCells" => new Table(
                data: $cells,
                columns: [
                    new ToolboxColumn("", fn (Cell $cell) => new Toolbox([
                        new ViewTool(
                            path: $this->generateUrl("app_cell_view_number", ["cellNumber" => $cell->getCellNumber()]),
                            icon: "cell",
                            iconStack: "view",
                        ),
                    ])),
                    new Column("Number", fn(Cell $cell) => $cell->getCellNumber()),
                    new Column("Name", fn(Cell $cell) => $cell->getName()),
                    new ComponentColumn("Methods", function(Cell $cell) use ($protein) {
                        $cellularProtein = $cell->getCellProteins()->filter(fn(CellProtein $cellProtein) => $cellProtein->getAssociatedProtein() === $protein)->first();

                        $detections = [];
                        foreach ($cellularProtein->getDetection() as $detection) {
                            if ($detection instanceof DetectionEntry) {
                                $detections[$detection->getMethod()] = ["boolean", $detection->getIsDetectable()];
                            } else {
                                $detections[$detection["method"]] = ["boolean", $detection["isDetectable"]];
                            }
                        }

                        if (count($detections) === 0) {
                            $detections = ["Method" => null];
                        }

                        return [
                            Metadata::class, [
                                "md" => 1, "xl" => 1,
                                "data" => $detections,
                            ]
                        ];
                    })
                ]
            )
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

    #[Route("/substance/import/{type}", name: "app_substance_import", methods: ["GET"], priority: 1)]
    #[IsGranted("ROLE_USER")]
    public function import(
        Security $security,
        string $type,
    ): Response {
        /** @var User $user */
        $user = $security->getUser();

        $data = [
            "substance" => [
                "owner" => $user,
                "group" => $user->getGroup(),
                "privacyLevel" => PrivacyLevel::Group,
            ],
            "lot" => [
                "owner" => $user,
                "group" => $user->getGroup(),
                "privacyLevel" => PrivacyLevel::Group,
                "numberOfAliquotes" => 1,
                "maxNumberOfAliquots" => 1,
            ]
        ];

        $substanceFormImportType = match($type) {
            "oligo" => ImportOligoType::class,
            default => $this->createNotFoundException("Unsupported substance type.")
        };

        $builder = $this->createFormBuilder($data);
        $builder
            ->add("substance", $substanceFormImportType, [

            ])
            ->add("lot", ImportLotType::class, [

            ])
        ;

        $form = $builder->getForm();


        return $this->render("parts/substance/import.html.twig", [
            "importForm" => $form,
            "postUrl" => $this->generateUrl("app_substance_import_post", ["type" => $type]),
        ]);
    }

    #[Route("/substance/import/{type}/post", name: "app_substance_import_post", methods: ["POST"])]
    #[IsGranted("ROLE_USER")]
    public function postImport(
        Request $request,
        Security $security,
        ValidatorInterface $validator,
        UserRepository $userRepository,
        LotRepository $lotRepository,
        BoxRepository $boxRepository,
        UserGroupRepository $userGroupRepository,
        EntityManagerInterface $entityManager,
        string $type,
    ): Response {
        $data = $request->toArray();

        $validateOnly = $data["options"]["validateOnly"] ?? false;
        $ignoreErrors = $data["options"]["ignoreErrors"] ?? false;

        $substanceClass = match($type) {
            "oligo" => Oligo::class,
            default => $this->createNotFoundException("Unsupported substance type"),
        };

        /** @var OligoRepository $substanceRepository */
        $substanceRepository = $entityManager->getRepository($substanceClass);

        $answer = [
            "numRows" => count($data["data"]),
            "numRowsCreated" => 0,
            "numRowsValid" => 0,
        ];

        $answerErrors = [];

        foreach ($data["data"] as $rowNumber => $dataRow) {
            $substance = $substanceRepository::createFromArray($userRepository, $userGroupRepository, $dataRow["substance"]);
            $lot = $lotRepository::createFromArray($userRepository, $userGroupRepository, $boxRepository, $dataRow["lot"]);

            // Must be first, or else the validation for the box coordinates fails
            $substance->addLot($lot);

            $violations = $validator->validate($substance);
            #$lotViolations = $validator->validate($lot);

            if (count($violations) > 0) {
                if (empty($answer["errors"])) {
                    $answer["errors"] = [];
                }

                $violationAnswer = [];

                /** @var ConstraintViolation $violation */
                foreach ($violations as $violation) {
                    $violationAnswer[] = [
                        "row" => $rowNumber,
                        "path" => $violation->getPropertyPath(),
                        "message" => $violation->getMessage(),
                    ];
                }

                $answerErrors[] = $violationAnswer;
            } else {
                if (!$validateOnly) {
                    $entityManager->persist($substance);
                    $answer["numRowsCreated"] += 1;
                }

                $answer["numRowsValid"] += 1;
            }
        }

        $flush = false;
        if (count($answerErrors) > 0) {
            $answer["errors"] = $answerErrors;
            if ($ignoreErrors and !$validateOnly) {
                $flush = true;
            } else {
                $answer["numRowsCreated"] = 0;
            }
        } elseif(!$validateOnly) {
            $flush = true;
        } else {
            $answer["numRowsCreated"] = 0;
        }

        if ($flush) {
            try {
                $entityManager->flush();
            } catch (UniqueConstraintViolationException $e) {
                $answer["db_errors"] = [
                    "type" => "UniqueConstraintViolation",
                    "message" => $e->getMessage()
                ];
                $answer["numRowsCreated"] = 0;
            }
        }

        return $this->json($answer);
    }
}