<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\StockManagement\Consumable;
use App\Entity\DoctrineEntity\StockManagement\ConsumableCategory;
use App\Entity\DoctrineEntity\StockManagement\ConsumableLot;
use App\Entity\DoctrineEntity\User\User;
use App\Form\StockKeeping\ConsumableCategoryType;
use App\Form\StockKeeping\ConsumableLotType;
use App\Form\StockKeeping\ConsumableType;
use App\Form\StockKeeping\QuickOrderType;
use App\Genie\Enums\Availability;
use App\Genie\Enums\PrivacyLevel;
use App\Repository\StockKeeping\ConsumableCategoryRepository;
use App\Service\FileUploader;
use DateTime;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

class ConsumableController extends AbstractController
{
    #[Route("/consumables", name: "app_consumables")]
    #[Route("/consumables/view/{category}", name: "app_consumables_category_view")]
    public function consumable_category(
        ConsumableCategoryRepository $categoryRepository,
        ?ConsumableCategory $category = null,
    ): Response {
        return $this->consumableHelper($categoryRepository, $category);
    }

    #[Route("/consumables/item/{consumable}", name: "app_consumables_item_view")]
    public function consumable(
        Request $request,
        #[CurrentUser]
        User $user,
        EntityManagerInterface $entityManager,
        ConsumableCategoryRepository $categoryRepository,
        Consumable $consumable,
    ): Response {
        $category = $consumable->getCategory();

        $data = [
            "times" => 1,
            "numberOfUnits" => $consumable->getNumberOfUnits(),
            "unitSize" => $consumable->getUnitSize(),
            "price" => $consumable->getPricePerPackage(),
            "status" => Availability::Ordered,
            "location" => $consumable->getLocation(),
        ];
        $quickOrderForm = $this->createForm(QuickOrderType::class, $data, [
            "save_button" => true,
            "save_label" => "Register",
        ]);
        $emptyForm = clone $quickOrderForm;
        $quickOrderForm->handleRequest($request);

        if ($quickOrderForm->isSubmitted() and $quickOrderForm->isValid()) {
            $data = $quickOrderForm->getData();
            $lotCount = $this->quickOrder($entityManager, $user, $consumable, $data);

            try {
                $entityManager->flush();
                $this->addFlash("success", "Successfully created {$lotCount}");
                $quickOrderForm = $emptyForm;

                return $this->redirectToRoute("app_consumables_item_view", ["consumable" => $consumable->getId()]);
            } catch (\Error $e) {
                $this->addFlash("error", "An error occured while creating the lots: {$e->getMessage()}");
            }
        }

        return $this->consumableHelper($categoryRepository, $category, $consumable, options: [
            "quickOrderForm" => $quickOrderForm,
        ]);
    }

    #[Route("/consumables/lot/{lot}", name: "app_consumables_lot_view")]
    public function consumableLot(
        Request $request,
        #[CurrentUser]
        User $user,
        EntityManagerInterface $entityManager,
        ConsumableCategoryRepository $categoryRepository,
        ConsumableLot $lot,
    ): Response {
        $consumable = $lot->getConsumable();
        $category = $consumable->getCategory();

        return $this->consumableHelper($categoryRepository, $category, $consumable, $lot, options: [
        ]);
    }

    private function consumableHelper(
        ConsumableCategoryRepository $categoryRepository,
        ?ConsumableCategory $category = null,
        ?Consumable $consumable = null,
        ?ConsumableLot $lot = null,
        array $options = [],
    ): Response {
        return $this->render("parts/consumables/consumables.html.twig", [
            "categories" => $categoryRepository->findAllWithConsumablesAndLots(),
            "currentCategory" => $category,
            "currentConsumable" => $consumable,
            "currentLot" => $lot,
            ... $options,
        ]);
    }

    private function quickOrder(
        EntityManagerInterface $entityManager,
        User $user,
        Consumable $consumable,
        mixed $data,
    ): int {
        $lotCount = 0;

        for ($i = 0; $i < $data["times"]; $i++) {
            $lot = $consumable->createLot();
            $lot->setNumberOfUnits($data["numberOfUnits"]);
            $lot->setUnitSize($data["unitSize"]);
            $lot->setPricePerPackage($data["price"]);
            $lot->setBoughtBy($user);
            $lot->setAvailability($data["status"]);
            $lot->setBoughtOn(new DateTime("now"));

            if ($data["location"]) {
                $lot->setLocation($data["location"]);
            }

            $lotIdentifier = $data["lotIdentifier"] ?? date("ymd");
            if ($data["times"] > 1) {
                $lot->setLotIdentifier($lotIdentifier . ".{$lotCount}");
            } else {
                $lot->setLotIdentifier($lotIdentifier);
            }

            $consumable->addLot($lot);
            $entityManager->persist($lot);
            $lotCount++;
        }

        return $lotCount;
    }

    #[Route("/consumables/consume/{lot}", name: "app_consumables_lot_consume")]
    public function quickLotConsume(
        EntityManagerInterface $entityManager,
        ConsumableLot $lot,
    ): Response {
        $consumable = $lot->getConsumable();
        $isNowEmpty = False;

        try {
            // If the package is pristine, we also set the opened date
            // We should do this up here because the code throws an exception if consumption is not possible, and
            // having this further down would require to duplicate this line.
            if ($lot->isPristine()) {
                $lot->setOpenedOn(new DateTime("now"));
            }

            if ($consumable->isConsumePackage()) {
                if ($lot->getUnitsConsumed() >= $lot->getNumberOfUnits()) {
                    throw new Exception("There are no packages left.");
                }

                $lot->consumeUnit(1);

                if ($lot->getUnitsConsumed() == $lot->getNumberOfUnits()) {
                    $lot->setAvailability(Availability::Empty);
                    $isNowEmpty = true;
                }
            } else {
                if ($lot->getTotalAvailablePieces() <= 0) {
                    throw new Exception("There are no pieces left to consume.");
                }

                $lot->consumePiece(1);

                if ($lot->getTotalAvailablePieces() == 0) {
                    $lot->setAvailability(Availability::Empty);
                    $isNowEmpty = true;
                }
            }

            $entityManager->flush();
            $this->addFlash("success", "Consumption complete." . ($isNowEmpty ? " The lot is now empty." : ""));
        } catch (DBALException $e) {
            $this->addFlash("error", "Consumption was not possible due to a database error.");
        } catch (Exception $e) {
            $this->addFlash("error", $e->getMessage());
        }

        return $this->redirectToRoute("app_consumables_item_view", ["consumable" => $consumable->getId()]);
    }

    #[Route("/consumables/makeAvailable/{lot}", name: "app_consumables_lot_makeAvailable")]
    public function quickLotMakeAvailable(
        EntityManagerInterface $entityManager,
        ConsumableLot $lot,
    ): Response {
        if ($lot->getAvailability() == Availability::Empty) {
            $this->addFlash("error", "Cannot make lot {$lot->getLotIdentifier()} available as its empty.");
        } elseif ($lot->getAvailability() == Availability::Available) {
            $this->addFlash("info", "Cannot make lot {$lot->getLotIdentifier()} available as it already is.");
        } else {
            try {
                $lot->setAvailability(Availability::Available);
                $lot->setArrivedOn(new DateTime("now"));

                $entityManager->flush();
                $this->addFlash("info", "Lot {$lot->getLotIdentifier()} has been made available.");
            } catch (Exception $e) {
                $this->addFlash("error", $e->getMessage());
            }
        }

        return $this->redirectToRoute("app_consumables_item_view", ["consumable" => $lot->getConsumable()->getId()]);
    }

    #[Route("/consumables/trashLot/{lot}", name: "app_consumables_lot_trash")]
    public function quickLotTrash(
        EntityManagerInterface $entityManager,
        ConsumableLot $lot
    ): Response {
        if ($lot->getAvailability() == Availability::Empty) {
            $this->addFlash("error", "Cannot make lot {$lot->getLotIdentifier()} available as it is already empty");
        } else {
            try {
                $lot->setAvailability(Availability::Empty);

                if ($lot->getConsumable()->isConsumePackage()) {
                    $lot->setUnitsConsumed($lot->getNumberOfUnits());
                } else {
                    $lot->setPiecesConsumed($lot->getTotalAmountOfPieces());
                }

                $entityManager->flush();
                $this->addFlash("info", "Lot {$lot->getLotIdentifier()} has been trashed.");
            } catch (Exception $e) {
                $this->addFlash("error", $e->getMessage());
            }
        }

        return $this->redirectToRoute("app_consumables_item_view", ["consumable" => $lot->getConsumable()->getId()]);
    }

    #[Route("/consumables/new", name: "app_consumables_category_new")]
    #[Route("/consumables/edit/{category}", name: "app_consumables_category_edit")]
    public function addOrEditConsumableCategory(
        Request $request,
        #[CurrentUser]
        User $user,
        EntityManagerInterface $entityManager,
        ?ConsumableCategory $category = null,
    ): Response {
        $route = $request->attributes->get("_route");

        if ($route === "app_consumables_category_new") {
            $this->denyAccessUnlessGranted("new", "ConsumableCategory");
            $newEntity = true;
            $title = "Consumable Category :: New";
            $category = new ConsumableCategory();
            $category->setOwner($user);
            $category->setGroup($user->getGroup());
            $category->setPrivacyLevel(PrivacyLevel::Group);
        } else {
            $this->denyAccessUnlessGranted("edit", $category);
            $title = "Consumable Category :: Edit :: {$category->getLongName()}";
            $newEntity = false;
        }

        $formOptions = [
            "save_button" => true,
        ];

        $form = $this->createForm(ConsumableCategoryType::class, $category, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $entityManager->persist($category);

            try {
                $entityManager->flush();
                $this->addFlash("success", "Category was successfully persisted.");
                return $this->redirectToRoute("app_consumables_category_view", ["category" => $category->getId()]);
            } catch (Exception $e) {
                $this->addFlash("error", "An error occured while persisting this entity to the database: {$e->getMessage()}");
            }
        }

        return $this->render("parts/forms/add_or_edit.html.twig", [
            "form" => $form,
            "title" => $title,
            "returnTo" => $this->generateUrl("app_consumables_category_view", ["category" => $category->getId()]),
        ]);
    }

    #[Route("/consumables/addTo/{category}", name: "app_consumables_item_add_to")]
    #[Route("/consumables/editItem/{consumable}", name: "app_consumables_item_edit")]
    public function addOrEditConsumable(
        Request $request,
        #[CurrentUser]
        User $user,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        ConsumableCategory $category = null,
        Consumable $consumable = null,
    ): Response {
        if (!$consumable and !$category) {
            $this->createNotFoundException("Not found.");
        }

        $route = $request->attributes->get("_route");

        if ($route === "app_consumables_item_add_to") {
            $this->denyAccessUnlessGranted("add_to", $category);
            $newEntity = true;
            $title = "Consumable Category :: Add To :: {$category->getLongName()}";
            $consumable = (new Consumable())
                ->setOwner($user)
                ->setGroup($user->getGroup())
                ->setPrivacyLevel(PrivacyLevel::Group)
                ->setCategory($category)
            ;
        } else {
            $newEntity = false;
            $title = "Consumable :: Edit :: {$consumable->getLongName()}";
            $category = $consumable->getCategory();
        }

        $formOptions = [
            "save_button" => true,
        ];

        $form = $this->createForm(ConsumableType::class, $consumable, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $fileUploader->upload($form);
            $fileUploader->updateFileSequence($consumable);
            $entityManager->persist($consumable);

            try {
                $entityManager->flush();
                $this->addFlash("success", "The consumable was successfully saved.");

                if ($newEntity) {
                    return $this->redirectToRoute("app_consumables_category_view", ["category" => $category->getId()]);
                } else {
                    return $this->redirectToRoute("app_consumables_item_view", ["consumable" => $consumable->getId()]);
                }
            } catch (\Error $e) {
                $this->addFlash("error", "Something went wrong: {$e->getMessage()}");
            }
        }

        return $this->render("parts/forms/add_or_edit.html.twig", [
            "form" => $form,
            "title" => $title,
            "returnTo" => $this->generateUrl("app_consumables"),
        ]);
    }

    #[Route("/consumables/addLot/{consumable}", name: "app_consumables_item_add_lot")]
    #[Route("/consumables/editLot/{lot}", name: "app_consumables_lot_edit")]
    public function addOrEditConsumableLot(
        Request $request,
        #[CurrentUser]
        User $user,
        EntityManagerInterface $entityManager,
        Consumable $consumable = null,
        ConsumableLot $lot = null,
    ): Response {
        if (!$consumable and !$lot) {
            $this->createNotFoundException("Not found.");
        }

        $route = $request->attributes->get("_route");

        if ($route === "app_consumables_item_add_lot") {
            $this->denyAccessUnlessGranted("add_lot", $consumable);
            $newEntity = true;
            $title = "Consumable :: {$consumable->getLongName()} :: Add Lot";
            $lot = $consumable->createLot()
                ->setBoughtBy($user)
                ->setBoughtOn(new DateTime("now"));
        } else {
            $newEntity = false;
            $consumable = $lot->getConsumable();
            $title = "Consumable :: {$consumable->getLongName()} :: Edit Lot :: {$lot->getLotIdentifier()}";
        }

        $formOptions = [
            "save_button" => true,
        ];

        $form = $this->createForm(ConsumableLotType::class, $lot, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $entityManager->persist($lot);

            try {
                $entityManager->flush();
                $this->addFlash("success", "The consumable lot was successfully saved.");

                return $this->redirectToRoute("app_consumables_item_view", ["consumable" => $consumable->getId()]);
            } catch (\Error $e) {
                $this->addFlash("error", "Something went wrong: {$e->getMessage()}");
            }
        }

        return $this->render("parts/forms/add_or_edit.html.twig", [
            "form" => $form,
            "title" => $title,
            "returnTo" => $this->generateUrl("app_consumables_item_view", ["consumable" => $consumable->getId()]),
        ]);
    }
}