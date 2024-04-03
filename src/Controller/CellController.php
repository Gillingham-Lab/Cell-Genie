<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\BoxMap;
use App\Entity\DoctrineEntity\Cell\Cell;
use App\Entity\DoctrineEntity\Cell\CellAliquot;
use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\DoctrineEntity\Cell\CellCultureEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureOtherEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureSplittingEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureTestEvent;
use App\Entity\DoctrineEntity\Cell\CellGroup;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Toolbox\AddTool;
use App\Entity\Toolbox\ClipwareTool;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Tool;
use App\Entity\Toolbox\Toolbox;
use App\Form\Cell\CellAliquotType;
use App\Form\Cell\CellCultureEvents\CellCultureEventTestType;
use App\Form\Cell\CellCultureEvents\CellCultureOtherType;
use App\Form\Cell\CellCultureEvents\CellCultureSplittingType;
use App\Form\Cell\CellCultureType;
use App\Form\Cell\CellGroupType;
use App\Form\Cell\CellType;
use App\Form\Search\CellSearchType;
use App\Repository\BoxRepository;
use App\Repository\Cell\CellAliquotRepository;
use App\Repository\Cell\CellCultureRepository;
use App\Repository\Cell\CellGroupRepository;
use App\Repository\Cell\CellRepository;
use App\Repository\ExperimentTypeRepository;
use App\Repository\Substance\ChemicalRepository;
use App\Repository\Substance\ProteinRepository;
use App\Security\Voter\Cell\CellAliquotVoter;
use App\Security\Voter\Cell\CellCultureVoter;
use App\Security\Voter\Cell\CellGroupVoter;
use App\Security\Voter\Cell\CellVoter;
use App\Service\Cells\CellCultureService;
use App\Service\FileUploader;
use App\Service\Storage\StorageBoxService;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CellController extends AbstractController
{
    private ?User $user = null;

    public function __construct(
        readonly private Security                 $security,
        readonly private CellRepository           $cellRepository,
        readonly private BoxRepository            $boxRepository,
        readonly private CellAliquotRepository    $cellAliquotRepository,
        readonly private ChemicalRepository       $chemicalRepository,
        readonly private ProteinRepository        $proteinRepository,
        readonly private ExperimentTypeRepository $experimentTypeRepository,
        readonly private EntityManagerInterface   $entityManager,
        readonly private CellCultureRepository    $cellCultureRepository,
    ) {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $this->user = $user;
        }
    }

    #[Route("/cells", name: "app_cells")]
    #[Route("/cells/group/view/{cellGroup}", name: "app_cells_group")]
    public function cells(
        CellGroupRepository $cellGroupRepository,
        CellGroup $cellGroup = null,
    ): Response {
        return $this->render('parts/cells/cells.html.twig', [
            "cellGroups" => $cellGroupRepository->getGroupsWithCellsAndAliquots(["name" => "ASC"]),
            "currentGroup" => $cellGroup,
        ]);
    }

    #[Route("/cells/all", name: "app_cells_all")]
    public function allCells(
        CellRepository $cellRepository
    ) {
        $cells = $this->cellRepository->getCellsWithAliquotes(
            orderBy: ["cellNumber" => "ASC"],
        );

        return $this->render("parts/cells/cells_list.html.twig", [
            "cells" => $cells,
            "entityClass" => Cell::class,
            "entityContext" => "cell",
            "liveSearchFormType" => CellSearchType::class,
       ]) ;
    }

    #[Route("/cells/search", name: "app_cells_search")]
    public function searchCell(
        CellGroupRepository $cellGroupRepository,
        CellRepository $cellRepository,
        Request $request,
    ): Response {
        $searchTerm = $request->request->get("search", null);

        if (!$searchTerm) {
            $this->addFlash("error", "Search term was empty.");
            return $this->redirectToRoute("app_cells");
        } elseif (strlen($searchTerm) < 3) {
            $this->addFlash("error", "Search term must contain at least 3 characters");
            return $this->redirectToRoute("app_cells");
        }

        $cellGroups = $cellGroupRepository->searchGroupsWithCellsAndAliquots($searchTerm, ["name" => "ASC"]);
        $cells = $cellRepository->searchCellsWithAliquots($searchTerm, ["cellNumber" => "ASC"]);

        return $this->render("parts/cells/cells.html.twig", [
            "cellGroups" => $cellGroups,
            "cells" => $cells,
            "currentGroup" => null,
            "searchTerm" => $searchTerm,
        ]);
    }

    #[Route("/cells/group/remove/{cellGroup}", name: "app_cells_group_remove")]
    #[IsGranted(CellGroupVoter::REMOVE, "cellGroup")]
    public function removeCellGroup(
        EntityManagerInterface $entityManager,
        CellGroup $cellGroup = null,
    ): Response {
        $this->denyAccessUnlessGranted(CellGroupVoter::REMOVE, $cellGroup);

        if ($cellGroup) {
            try {
                $entityManager->remove($cellGroup);
                $entityManager->flush();

                $this->addFlash("success", "Cell group was successfully removed.");
            } catch (\Exception $e) {
                $this->addFlash("error", "Cell group was not removed due to an error: {$e->getMessage()}");
            }
        }

        return $this->redirectToRoute("app_cells");
    }

    #[Route("/cells/group/add", name: "app_cells_group_add")]
    public function addCellGroup(
        Request $request,
        EntityManagerInterface $entityManager,
        CellGroupRepository $cellGroupRepository,
    ): Response {
        $this->denyAccessUnlessGranted(CellGroupVoter::NEW, "CellGroup");

        return $this->addNewOrEditCellGroup($request, $entityManager, $cellGroupRepository);
    }

    #[Route("/cells/group/edit/{cellGroup}", name: "app_cells_group_edit")]
    public function addNewOrEditCellGroup(
        Request $request,
        EntityManagerInterface $entityManager,
        CellGroupRepository $cellGroupRepository,
        CellGroup $cellGroup = null,
    ): Response {
        if ($cellGroup === null and $request->get("_route") === "app_cells_group_add") {
            $new = true;
            $cellGroup = new CellGroup();
        } elseif ($cellGroup === null) {
            throw $this->createNotFoundException();
        } else {
            $new = false;

            $this->denyAccessUnlessGranted(CellGroupVoter::EDIT, $cellGroup);
        }

        $formType = CellGroupType::class;
        $formOptions = [
            "save_button" => true,
        ];

        $form = $this->createForm($formType, $cellGroup, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            try {
                // Change data if left empty, but parent has the information
                $groupParent = $cellGroup->getParent();

                if ($groupParent !== null) {
                    $fields = ["organism", "morphology", "tissue", "isCancer", "age", "sex", "ethnicity", "disease"];

                    foreach ($fields as $field) {
                        $setter = "set$field";
                        $getter = "get$field";

                        if ($cellGroup->$getter() === null or $cellGroup->$getter() === "") {
                            $cellGroup->$setter($groupParent->$getter());
                        }
                    }
                }

                $entityManager->persist($cellGroup);
                $entityManager->flush();

                if ($new) {
                    $this->addFlash("success", "Cell group has been created.");
                } else {
                    $this->addFlash("success", "Cell group has been edited.");
                }

                return $this->redirectToRoute("app_cells_group", ["cellGroup" => $cellGroup->getId()]);
            } catch (\Exception $e) {
                $this->addFlash("error", "Something went wrong: {$e->getMessage()}");
            }
        }

        return $this->render("parts/forms/add_or_edit_cell_group.html.twig", [
            "cellGroup" => $cellGroup,
            "form" => $form,
            "returnTo" => $new
                ? $this->generateUrl("app_cells")
                : $this->generateUrl("app_cells_group", ["cellGroup" => $cellGroup->getId()->toBase32()]),
        ]);
    }

    #[Route("/cells/view/no/{cellNumber}", name: "app_cell_view_number")]
    #[Route("/cells/view/no/{cellNumber}/{aliquotId}", name: "app_cell_aliquot_view_number")]
    #[IsGranted("view", "cell")]
    #[IsGranted("view", "aliquot")]
    public function viewCellByNumber(
        StorageBoxService $boxService,
        #[MapEntity(mapping: ["cellNumber" => "cellNumber"])]
        Cell $cell,
        #[MapEntity(mapping: ["aliquotId" => "id"])]
        ?CellAliquot $aliquot,
    ): Response {
        return $this->viewCell($boxService, $cell, $aliquot);
    }

    #[Route("/cells/view/{cellId}", name: "app_cell_view")]
    #[Route("/cells/view/{cellId}/{aliquotId}", name: "app_cell_aliquot_view")]
    #[IsGranted("view", "cell")]
    #[IsGranted("view", "aliquot")]
    public function viewCell(
        StorageBoxService $boxService,
        #[MapEntity(mapping: ["cellId" => "id"])]
        Cell $cell,
        #[MapEntity(mapping: ["aliquotId" => "id"])]
        ?CellAliquot $aliquot,
    ): Response {
        // Get all boxes that contain aliquots of the current cell line
        $boxes = $boxService->getBoxes($cell);

        // Get corresponding box maps
        $boxMaps = $boxService->getBoxMaps($cell, $boxes);

        // Get associated chemicals
        $chemicals = $this->chemicalRepository->findByCell($cell);
        // Get associated proteins
        $proteins = $this->proteinRepository->findByCell($cell);
        // Get associated experiment types
        $experimentTypes = $this->experimentTypeRepository->findByCell($cell);

        $toolBox = new Toolbox([
            new Tool(
                path: $this->generateUrl("app_cells", ["cellGroup" => $cell->getCellGroup()->getId()]),
                icon: "cell",
                iconStack: "up",
                buttonClass: "btn-secondary",
                tooltip: "Browse cell group"
            ),
            new Tool(
                path: $this->generateUrl("app_cells_all"),
                icon: "cell",
                iconStack: "search",
                buttonClass: "btn-secondary",
                tooltip: "Search cells"
            ),
            new ClipwareTool(
                clipboardText: $cell->getName() . ($cell->getRrid() ? " (RRID:{$cell->getRrid()})" : ""),
                tooltip: "Copy citation on cell",
            ),
            new EditTool($this->generateUrl("app_cell_edit", ["cell" => $cell->getCellNumber()]), tooltip: "Edit cell", iconStack: "edit", icon: "cell"),
            new AddTool($this->generateUrl("app_cell_aliquot_add", ["cell" => $cell->getCellNumber()]), tooltip: "Add aliquot"),
        ]);

        return $this->render('parts/cells/cell.html.twig', [
            "toolbox" => $toolBox,
            "cell" => $cell,
            "boxes" => $boxes,
            //"boxAliquotes" => $boxAliquotes,
            "currentAliquot" => $aliquot,
            "boxMaps" => $boxMaps,
            "aliquote" => $aliquot,
            "chemicals" => $chemicals,
            "proteins" => $proteins,
            "experimentTypes" => $experimentTypes,
        ]);
    }

    #[Route("/cells/add", name: "app_cell_add")]
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
    public function addCell(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
    ): Response {
        $this->denyAccessUnlessGranted(CellVoter::NEW, "Cell");

        return $this->addNewOrEditCell($request, $entityManager, $fileUploader);
    }

    #[Route("/cells/edit/{cell}", name: "app_cell_edit")]
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
    public function addNewOrEditCell(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        #[MapEntity(expr: "repository.findCellByIdOrNumber(cell)")]
        Cell $cell = null,
    ): Response {
        if (!$cell and $request->get("_route") === "app_cell_add") {
            $cell = new Cell();
            $new = true;

            // Set owner and owner group
            $cell->setOwner($this->user);
            $cell->setGroup($this->user?->getGroup());
        } elseif (!$cell) {
            throw $this->createNotFoundException("Cell has not been found");
        } else {
            $new = false;

            $this->denyAccessUnlessGranted(CellVoter::EDIT, $cell);
        }

        $formType = CellType::class;
        $formOptions = [
            "save_button" => true,
            "current_cell" => $new ? null : $cell,
        ];

        $form = $this->createForm($formType, $cell, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $fileUploader->upload($form);
            $fileUploader->updateFileSequence($cell);

            try {
                $i = 0;
                foreach ($cell->getCellProteins() as $cellProtein) {
                    $cellProtein->setOrderValue($i);
                    $i++;
                }

                $entityManager->persist($cell);
                $entityManager->flush();

                $this->addFlash("success", "Cell has been updated.");

                if ($cell->getCellNumber()) {
                    return $this->redirectToRoute("app_cell_view_number", ["cellNumber" => $cell->getCellNumber()]);
                } else {
                    return $this->redirectToRoute("app_cell_view", ["cellId" => $cell->getId()]);
                }
            } catch (\Exception $e) {
                $this->addFlash("error", "Something went wrong: {$e->getMessage()}.");
            }
        }

        return $this->render("parts/forms/add_or_edit_cell.html.twig", [
            "cell" => ($new ? null : $cell),
            "form" => $form,
            "returnTo" => $new
                ? $this->generateUrl("app_cells")
                : $this->generateUrl("app_cell_view_number", ["cellNumber" => $cell->getCellNumber()]),
        ]);
    }

    #[Route("/cells/addAliquot/{cell}", name: "app_cell_aliquot_add")]
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
    public function addNewCellAliquot(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        #[MapEntity(expr: 'repository.findCellByIdOrNumber(cell)')]
        Cell $cell = null,
    ): Response {
        if (!$cell) {
            throw $this->createNotFoundException("The request cell was not found");
        }

        $this->denyAccessUnlessGranted(CellVoter::ADD_ALIQUOT, $cell);

        return $this->addNewOrEditCellAliquot($request, $entityManager, $fileUploader, $cell, null);
    }

    #[Route("/cells/editAliquot/{cell}/{cellAliquot}", name: "app_cell_aliquot_edit")]
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
    public function addNewOrEditCellAliquot(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        #[MapEntity(expr: 'repository.findCellByIdOrNumber(cell)')]
        Cell $cell,
        CellAliquot $cellAliquot = null,
    ): Response {
        if (!$cell) {
            throw $this->createNotFoundException("The request cell was not found");
        }

        if (!$cellAliquot and $request->get("_route") === "app_cell_aliquot_add") {
            $cellAliquot = new CellAliquot();
            $cellAliquot->setCell($cell);
            $cellAliquot->setOwner($this->user);
            $cellAliquot->setGroup($this->user?->getGroup());
            $new = true;
        } elseif (!$cellAliquot) {
            throw $this->createNotFoundException("Cell aliquot has not been found.");
        } else {
            $new = false;

            if ($cellAliquot->getCell() !== $cell) {
                throw $this->createNotFoundException("No aliquot with the id '{$cellAliquot->getId()}' has been found for the given cell line.");
            }

            $this->denyAccessUnlessGranted(CellAliquotVoter::EDIT, $cellAliquot);
        }

        $formType = CellAliquotType::class;
        $formOptions = [
            "save_button" => true,
            "current_cell" => $cell,
            "current_aliquot" => $new ? null : $cellAliquot,
        ];

        $form = $this->createForm($formType, $cellAliquot, $formOptions);
        $form->handleRequest($request);

        if ($new) {
            $cellAliquot->setGroup($this->getUser()->getGroup());
            $cellAliquot->setOwner($this->getUser());
        }

        if ($form->isSubmitted() and $form->isValid()) {
            // @ToDo: Add attachments to CellAliquots.
            //$fileUploader->upload($form);
            //$fileUploader->updateSequence($cellAliquot);

            try {
                $entityManager->persist($cellAliquot);
                $entityManager->flush();

                $this->addFlash("success", "Cell aliquot has been updated.");

                if ($cell->getCellNumber()) {
                    return $this->redirectToRoute("app_cell_aliquot_view_number", ["cellNumber" => $cell->getCellNumber(), "aliquotId" => $cellAliquot->getId()]);
                } else {
                    return $this->redirectToRoute("app_cell_aliquot_view", ["cellId" => $cell->getId(), "aliquotId" => $cellAliquot->getId()]);
                }
            } catch (\Exception $e) {
                $this->addFlash("error", "Something went wrong: {$e->getMessage()}.");
            }
        }

        return $this->render("parts/forms/add_or_edit_cell_aliquot.html.twig", [
            "cell" => $cell,
            "aliquot" => $new ? null : $cellAliquot,
            "form" => $form,
            "returnTo" => $new
                ? $this->generateUrl("app_cell_view_number", ["cellNumber" => $cell->getCellNumber()])
                : $this->generateUrl("app_cell_aliquot_view_number", ["cellNumber" => $cell->getCellNumber(), "aliquotId" => $cellAliquot->getId()]),
        ]);
    }


    #[Route("/cells/consume/{aliquotId}", name: "app_cell_consume_aliquot")]
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
    // #ToDo: Change this so that a form to create the cell culture is shown instead of silently adding a culture.
    public function consumeAliquot(
        #[MapEntity(mapping: ["aliquotId"  => "id"])]
        CellAliquot $aliquot,
        #[CurrentUser]
        User $user,
        CellCultureService $cellCultureService,
    ): Response {
        $this->denyAccessUnlessGranted("consume", $aliquot);

        if ($aliquot->getVials() <= 0) {
            $this->addFlash("error", "There are no aliquote left to consume.");
        } else {
            // For updating legacy vials.
            if ($aliquot->getMaxVials() === null) {
                $aliquot->setMaxVials($aliquot->getVials());
            }

            // Reduce aliquot numbers by 1
            $aliquot->setVials($aliquot->getVials() - 1);

            if ($aliquot->getCell()->isAliquotConsumptionCreatesCulture()) {
                // Create new cell culture
                $cellCulture = $cellCultureService->createCellCulture($user, $aliquot);

                // Flush changes
                $this->entityManager->flush();

                $this->addFlash("success", "Aliquot was consumed and a new cell culture has been created. Check out the current cell cultures!");

                return $this->redirectToRoute("app_cell_culture_edit", [
                    "cellCulture" => $cellCulture->getId(),
                ]);
            } else {
                // Flush changes
                $this->entityManager->flush();

                $this->addFlash("success", "Aliquot was consumed.");
            }
        }

        return $this->redirectToRoute("app_cell_aliquot_view", [
            "cellId" => $aliquot->getCell()->getId(),
            "aliquotId" => $aliquot->getId(),
        ]);
    }

    #[Route("/cells/aliquot/trash/{aliquotId}", name: "app_cell_trash_aliquot")]
    #[IsGranted("remove", "aliquot")]
    public function trashAliquot(
        EntityManagerInterface $entityManager,
        #[MapEntity(mapping: ["aliquotId"  => "id"])]
        CellAliquot $aliquot,
    ): Response {
        try {
            $entityManager->remove($aliquot);
            $entityManager->flush();
            $this->addFlash("success", "Aliquot was removed.");
        } catch (\Exception $e) {
            $this->addFlash("error", "Aliquot was not removed. Reason: {$e->getMessage()}");
        }

        return $this->redirectToRoute("app_cell_view_number", ["cellNumber" => $aliquot->getCell()->getCellNumber()]);
    }

    #[Route("/cells/cultures", name: "app_cell_cultures")]
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
    public function cellCultures(Request $request): Response
    {
        $startDate = DateTimeImmutable::createFromFormat("Y-m-d", $request->get("startDate") ?? "");
        $endDate = DateTimeImmutable::createFromFormat("Y-m-d", $request->get("endDate") ?? "");

        $filterScientist = $request->get("scientist");
        $filterIncubator = $request->get("incubator");

        if ($startDate === false and $endDate === false) {
            $startDate = new DateTimeImmutable("today - 3 weeks");
            $endDate = new DateTimeImmutable("today + 1 weeks");
        } elseif ($startDate === false) {
            $startDate = $endDate->sub(new DateInterval("P4W"));
        } elseif ($endDate === false) {
            $endDate = $startDate->add(new DateInterval("P4W"));
        }

        $currentCultures = $this->cellCultureRepository->findAllBetween($startDate, $endDate, $filterIncubator, $filterScientist);

        $cultures = [];
        /** @var CellCulture $culture */
        foreach ($currentCultures as $culture) {
            // Skip if already set
            if (isset($cultures[$culture->getId()->toBase58()])) {
                continue;
            }

            // Skip if it has a parent culture registered (for group reasons).
            if ($culture->getParentCellCulture() !== null) {
                continue;
            }

            // Add
            $cultures[$culture->getId()->toBase58()] = $culture;

            // Now we add all child cultures of the current culture.
            $this->extractCultures($currentCultures, $cultures, $culture);
        }

        return $this->render("parts/cells/cell_cultures.html.twig", [
            "cultures" => array_values($cultures),
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);
    }

    #[Route("/cells/cultures/trash/{cellCulture}", name: "app_cell_culture_trash")]
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
    public function trashCellCulture(Request $request, CellCulture $cellCulture): Response
    {
        $this->denyAccessUnlessGranted(CellCultureVoter::TRASH, $cellCulture);

        try {
            $cellCulture->setTrashedOn(new DateTime("today"));

            $this->entityManager->flush();
            $this->addFlash("success", "Cell culture was successfully marked as trashed.");
        } catch (Exception $exception) {
            $this->addFlash("error", "Cell culture was not able to be trashed. Reason: {$exception->getMessage()}");
        }

        if ($request->get("redirect") === "cellCulture") {
            return $this->redirectToRoute("app_cell_culture_view", ["cellCulture" => $cellCulture->getId()]);
        }

        return $this->redirectToRoute("app_cell_cultures");
    }

    #[Route("/cells/cultures/restore/{cellCulture}", name: "app_cell_culture_restore")]
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
    public function restoreCellCulture(Request $request, CellCulture $cellCulture): Response
    {
        $this->denyAccessUnlessGranted(CellCultureVoter::TRASH, $cellCulture);

        try {
            $cellCulture->setTrashedOn(null);

            $this->entityManager->flush();
            $this->addFlash("success", "Cell culture was successfully marked as restored.");
        } catch (Exception $exception) {
            $this->addFlash("error", "Cell culture was not able to be restored. Reason: {$exception->getMessage()}");
        }

        if ($request->get("redirect") === "cellCulture") {
            return $this->redirectToRoute("app_cell_culture_view", ["cellCulture" => $cellCulture->getId()]);
        }

        return $this->redirectToRoute("app_cell_cultures");
    }

    #[Route("/cells/cultures/create/{cellCulture}/event/{eventType}", name: "app_cell_culture_create_event", requirements: ["eventType" => "test|split|other"])]
    #[Route("/cells/cultures/edit/{cellCulture}/event/{cellCultureEvent}", name: "app_cell_culture_edit_event")]
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
    public function addCellCultureEvent(
        Request $request,
        CellCulture $cellCulture,
        ?string $eventType = null,
        ?CellCultureEvent $cellCultureEvent = null
    ): Response {
        $this->denyAccessUnlessGranted(CellCultureVoter::ADD_EVENT, $cellCulture);

        if ($cellCulture->getTrashedOn()) {
            $this->addFlash("error", "Cannot add events for trashed cell cultures.");
            return $this->redirectToRoute("app_cell_cultures");
        }

        if ($eventType !== null) {
            $formType = match($eventType) {
                "test" => CellCultureEventTestType::class,
                "split" => CellCultureSplittingType::class,
                "other" => CellCultureOtherType::class,
            };

            $entityType = match($eventType) {
                "test" => CellCultureTestEvent::class,
                "split" => CellCultureSplittingEvent::class,
                "other" => CellCultureOtherEvent::class,
            };

            /** @var CellCultureEvent $cellCultureEvent */
            $cellCultureEvent = new $entityType();
        } else {
            $formType = match(get_class($cellCultureEvent)) {
                CellCultureTestEvent::class => CellCultureEventTestType::class,
                CellCultureSplittingEvent::class => CellCultureSplittingType::class,
                CellCultureOtherEvent::class => CellCultureOtherType::class,
            };
        }

        // Set owner if null
        $currentUser = $this->security->getUser();
        if ($currentUser instanceof User and $cellCultureEvent->getOwner() === null) {
            $cellCultureEvent->setOwner($currentUser);
        }

        // Set cell culture if null
        if ($cellCultureEvent->getCellCulture() === null) {
            $cellCultureEvent->setCellCulture($cellCulture);
        }

        $formOptions = [
            "save_button" => true,
        ];

        if ($eventType === "split") {
            $formOptions["show_splits"] = true;
        }

        $form = $this->createForm($formType, $cellCultureEvent, $formOptions);

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            // $eventType is only set if a new entity is created. Therefore, this should not get called during an edit.
            if ($eventType === "split") {
                // Create new cell cultures if requested.
                if ($form["newCultures"]->getData() > 0) {
                    $numberOfNewCultures = min(10, $form["newCultures"]->getData());

                    for ($i = 0; $i < $numberOfNewCultures; $i++) {
                        $newCulture = new CellCulture();
                        $newCulture->setUnfrozenOn($cellCultureEvent->getDate());
                        $newCulture->setParentCellCulture($cellCulture);
                        $newCulture->setOwner($currentUser);
                        $newCulture->setFlask($cellCultureEvent->getNewFlask());
                        $newCulture->setIncubator($cellCulture->getIncubator());

                        $currentCellNumber = $cellCulture->getNumber();

                        if (strlen($currentCellNumber) > 8) {
                            $currentCellNumber = substr($currentCellNumber, 0, 8);
                        }

                        $newCulture->setNumber($currentCellNumber . ".{$i}");

                        $this->entityManager->persist($newCulture);
                    }
                }
            }

            if ($eventType) {
                // Persist is only necessary if $eventType is not null (otherwise it would be an edit)
                $this->entityManager->persist($cellCultureEvent);
            }

            $this->entityManager->flush();

            if ($eventType) {
                $this->addFlash("info", "Event has been created.");
            } else {
                $this->addFlash("info", "Event has been edited.");
            }

            if ($request->get("redirect") === "cellCulture") {
                return $this->redirectToRoute("app_cell_culture_view", ["cellCulture" => $cellCulture->getId()]);
            }

            return $this->redirectToRoute("app_cell_cultures");
        }

        return $this->render("parts/cells/cell_cultures_new_event.html.twig", [
            "culture" => $cellCulture,
            "form" => $form,
        ]);
    }

    #[Route("/cells/cultures/trash/{cellCulture}/{cellCultureEvent}", name: "app_cell_culture_trash_event")]
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
    public function trashCellCultureEvent(
        Request $request,
        CellCulture $cellCulture,
        CellCultureEvent $cellCultureEvent,
    ): Response {
        $this->denyAccessUnlessGranted(CellCultureVoter::REMOVE, $cellCultureEvent);
        $this->entityManager->remove($cellCultureEvent);

        try {
            $this->entityManager->flush();
            $this->addFlash("info", "Event was trashed.");
        } catch (\Exception $e) {
            $this->addFlash("error", "Event was not trashed. Reason: {$e->getMessage()}");
        }

        if ($request->get("redirect") === "cellCulture") {
            return $this->redirectToRoute("app_cell_culture_view", ["cellCulture" => $cellCulture->getId()]);
        }

        return $this->redirectToRoute("app_cell_cultures");
    }

    #[Route("/cells/cultures/view/{cellCulture}", name: "app_cell_culture_view")]
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
    public function viewCellCulture(Request $request, CellCulture $cellCulture): Response
    {
        $this->denyAccessUnlessGranted(CellCultureVoter::VIEW, $cellCulture);

        return $this->render("parts/cells/cell_culture.html.twig", [
            "culture" => $cellCulture,
            "startDate" => $cellCulture->getUnfrozenOn(),
            "endDate" => $cellCulture->getTrashedOn() ?? new DateTime("now + 1 week")
        ]);
    }

    #[Route("/cells/cultures/edit/{cellCulture}", name: "app_cell_culture_edit")]
    #[IsGranted("ROLE_USER", message: "You must be logged in to do this")]
    public function editCellCulture(Request $request, CellCulture $cellCulture): Response
    {
        $this->denyAccessUnlessGranted(CellCultureVoter::EDIT, $cellCulture);

        $form = $this->createForm(CellCultureType::class, $cellCulture, ["save_button" => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            // If the cell has been trashed before it was unfrozen, we set this to 0
            if ($cellCulture->getTrashedOn() <= $cellCulture->getUnfrozenOn()) {
                $cellCulture->setTrashedOn(null);
            }

            // Save the changes
            $this->entityManager->flush();

            $this->addFlash("info", "Culture has been edited.");

            if ($request->get("redirect") === "cellCulture") {
                return $this->redirectToRoute("app_cell_culture_view", ["cellCulture" => $cellCulture->getId()]);
            }

            return $this->redirectToRoute("app_cell_cultures");
        }

        return $this->render("parts/cells/cell_culture_edit.html.twig", [
            "culture" => $cellCulture,
            "form" => $form,
        ]);
    }

    private function extractCultures(array $currentCultures, array &$cultureList, CellCulture $parentCulture)
    {
        // Very bad at scaling (O(n^n)), but the lists are going to be short. Should be acceptable.
        /** @var CellCulture $culture */
        foreach ($currentCultures as $culture) {
            if ($culture->getParentCellCulture() !== $parentCulture) {
                continue;
            }

            $cultureList[$culture->getId()->toBase58()] = $culture;
            $this->extractCultures($currentCultures, $cultureList, $culture);
        }
    }
}
