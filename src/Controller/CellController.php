<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\Cell\CellCulture;
use App\Entity\DoctrineEntity\Cell\CellCultureEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureOtherEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureSplittingEvent;
use App\Entity\DoctrineEntity\Cell\CellCultureTestEvent;
use App\Entity\User;
use App\Form\CellCultureEventTestType;
use App\Form\CellCultureOtherType;
use App\Form\CellCultureSplittingType;
use App\Form\CellCultureType;
use App\Repository\BoxRepository;
use App\Repository\Cell\CellAliquotRepository;
use App\Repository\Cell\CellCultureRepository;
use App\Repository\Cell\CellRepository;
use App\Repository\ChemicalRepository;
use App\Repository\ExperimentTypeRepository;
use App\Repository\ProteinRepository;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class CellController extends AbstractController
{
    public function __construct(
        private Security $security,
        private CellRepository $cellRepository,
        private BoxRepository $boxRepository,
        private CellAliquotRepository $cellAliquoteRepository,
        private ChemicalRepository $chemicalRepository,
        private ProteinRepository $proteinRepository,
        private ExperimentTypeRepository $experimentTypeRepository,
        private EntityManagerInterface $entityManager,
        private CellCultureRepository $cellCultureRepository,
    ) {

    }

    #[Route("/cells", name: "app_cells")]
    public function cells(): Response
    {
        $cells = $this->cellRepository->getCellsWithAliquotes(
            orderBy: ["cellNumber" => "ASC"]
        );

        return $this->render('parts/cells/cells.html.twig', [
            "cells" => $cells,
        ]);
    }

    #[Route("/cells/view/{cellId}", name: "app_cell_view")]
    #[Route("/cells/view/no/{cellNumber}", name: "app_cell_view_number")]
    #[Route("/cells/view/{cellId}/{aliquoteId}", name: "app_cell_aliquote_view")]
    #[Route("/cells/view/no/{cellNumber}/{aliquoteId}", name: "app_cell_aliquote_view_number")]
    public function cell_overview(
        string $cellId = null,
        string $aliquoteId = null,
        string $cellNumber = null,
    ): Response {
        if ($cellId === null AND $cellNumber === null) {
            throw new NotFoundHttpException();
        }

        if ($cellId) {
            try {
                $cell = $this->cellRepository->find($cellId);
            } catch (ConversionException) {
                $cell = null;
            }
        }

        if ($cellNumber) {
            $cell = $this->cellRepository->findOneBy(["cellNumber" => $cellNumber]);
        }

        if (!$cell) {
            $this->addFlash("error", "Cell was not found");
            return $this->redirectToRoute("app_cells");
        }

        // Get all boxes that contain aliquotes of the current cell line
        $boxes = $this->boxRepository->findByAliquotedCell($cell);

        // Get all aliquotes from those boxes
        $aliquotes = $this->cellAliquoteRepository->findAllFromBoxes($boxes);

        // Create an associative array that makes box.id => aliquotes[]
        $boxAliquotes = [];
        foreach ($aliquotes as $aliquote) {
            $aliquoteBox = $aliquote->getBox();

            if (empty($boxAliquotes[$aliquoteBox->getId()])) {
                $boxAliquotes[$aliquoteBox->getId()] = [];
            }

            $boxAliquotes[$aliquoteBox->getId()][] = $aliquote;
        }

        if ($aliquoteId) {
            $aliquote = $this->cellAliquoteRepository->find($aliquoteId);
        } else {
            $aliquote = null;
        }

        // Get associated chemicals
        $chemicals = $this->chemicalRepository->findByCell($cell);
        // Get associated proteins
        $proteins = $this->proteinRepository->findByCell($cell);
        // Get associated experiment types
        $experimentTypes = $this->experimentTypeRepository->findByCell($cell);

        return $this->render('parts/cells/cell.html.twig', [
            "cell" => $cell,
            "boxes" => $boxes,
            "boxAliquotes" => $boxAliquotes,
            "aliquote" => $aliquote,
            "chemicals" => $chemicals,
            "proteins" => $proteins,
            "experimentTypes" => $experimentTypes,
        ]);
    }

    #[Route("/cells/consume/{aliquoteId}", name: "app_cell_consume_aliquote")]
    public function consumeAliquote($aliquoteId): Response
    {
        $aliquote = $this->cellAliquoteRepository->find($aliquoteId);

        if (!$aliquote) {
            $this->addFlash("error", "Aliquote does not exist.");
            return $this->redirectToRoute("app_cells");
        }

        if ($aliquote->getVials() <= 0) {
            $this->addFlash("error", "There are no aliquote left to consume.");
        } else {
            $aliquote->setVials($aliquote->getVials() - 1);
            $this->entityManager->flush();

            $this->addFlash("success", "Aliquote was consumed.");
        }

        return $this->redirectToRoute("app_cell_aliquote_view", [
            "cellId" => $aliquote->getCell()->getId(),
            "aliquoteId" => $aliquoteId
        ]);
    }

    #[Route("/cells/cultures", name: "app_cell_cultures")]
    public function cellCultures(Request $request): Response
    {
        $startDate = DateTimeImmutable::createFromFormat("Y-m-d", $request->get("startDate") ?? "");
        $endDate = DateTimeImmutable::createFromFormat("Y-m-d", $request->get("endDate") ?? "");

        if ($startDate === false and $endDate === false) {
            $startDate = new DateTimeImmutable("today - 3 weeks");
            $endDate = new DateTimeImmutable("today + 1 weeks");
        } elseif ($startDate === false) {
            $startDate = $endDate->sub(new DateInterval("P4W"));
        } elseif ($endDate === false) {
            $endDate = $startDate->add(new DateInterval("P4W"));
        }

        $currentCultures = $this->cellCultureRepository->findAllBetween($startDate, $endDate);

        $cultures = [];
        /** @var CellCulture $culture */
        foreach ($currentCultures as $culture) {
            if (isset($cultures[$culture->getId()->toBase58()])) {
                continue;
            }

            if ($culture->getParentCellCulture() !== null) {
                continue;
            }

            $cultures[$culture->getId()->toBase58()] = $culture;

            $this->extractCultures($cultures, $culture);
        }

        return $this->render("parts/cells/cell_cultures.html.twig", [
            "cultures" => array_values($cultures),
            "startDate" => $startDate,
            "endDate" => $endDate,
        ]);
    }

    #[Route("/cells/cultures/trash/{cellCulture}", name: "app_cell_culture_trash")]
    public function trashCellCulture(Request $request, CellCulture $cellCulture): Response
    {
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
    public function restoreCellCulture(Request $request, CellCulture $cellCulture): Response
    {
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

    #[Route("/cells/cultures/create/{cellCulture}/{eventType}", name: "app_cell_culture_create_event", requirements: ["eventType" => "test|split|other"])]
    #[Route("/cells/cultures/edit/{cellCulture}/{cellCultureEvent}", name: "app_cell_culture_edit_event")]
    public function addCellCultureEvent(
        Request $request,
        CellCulture $cellCulture,
        ?string $eventType = null,
        ?CellCultureEvent $cellCultureEvent = null
    ): Response {
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

        return $this->renderForm("parts/cells/cell_cultures_new_event.html.twig", [
            "culture" => $cellCulture,
            "form" => $form,
        ]);
    }

    #[Route("/cells/cultures/trash/{cellCulture}/{cellCultureEvent}", name: "app_cell_culture_trash_event")]
    public function trashCellCultureEvent(
        Request $request,
        CellCulture $cellCulture,
        CellCultureEvent $cellCultureEvent,
    ): Response {
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
    public function viewCellCulture(Request $request, CellCulture $cellCulture): Response
    {
        return $this->render("parts/cells/cell_culture.html.twig", [
            "culture" => $cellCulture,
            "startDate" => $cellCulture->getUnfrozenOn(),
            "endDate" => $cellCulture->getTrashedOn() ?? new DateTime("now + 1 week")
        ]);
    }

    #[Route("/cells/cultures/edit/{cellCulture}", name: "app_cell_culture_edit")]
    public function editCellCulture(Request $request, CellCulture $cellCulture): Response
    {
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

        return $this->renderForm("parts/cells/cell_culture_edit.html.twig", [
            "culture" => $cellCulture,
            "form" => $form,
        ]);
    }

    private function extractCultures(array &$cultures, CellCulture $culture)
    {
        foreach ($culture->getSubCellCultures() as $subCulture) {
            if (isset($cultures[$subCulture->getId()->toBase58()])) {
                continue;
            }

            $cultures[$subCulture->getId()->toBase58()] = $subCulture;

            $this->extractCultures($cultures, $subCulture);
        }
    }
}
