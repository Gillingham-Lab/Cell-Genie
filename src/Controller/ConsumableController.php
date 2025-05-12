<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\StockManagement\Consumable;
use App\Entity\DoctrineEntity\StockManagement\ConsumableCategory;
use App\Entity\DoctrineEntity\StockManagement\ConsumableLot;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Embeddable\Price;
use App\Entity\Toolbox\AddTool;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Toolbox;
use App\Form\StockKeeping\ConsumableCategoryType;
use App\Form\StockKeeping\ConsumableLotType;
use App\Form\StockKeeping\ConsumableType;
use App\Form\StockKeeping\QuickOrderType;
use App\Genie\Enums\Availability;
use App\Genie\Enums\PrivacyLevel;
use App\Repository\StockKeeping\ConsumableCategoryRepository;
use App\Repository\StockKeeping\ConsumableRepository;
use App\Security\Voter\ConsumableVoter;
use App\Service\FileUploader;
use App\Service\TreeView\ConsumableTreeViewService;
use DateTime;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
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

    #[Route("/consumables/item/{category}/{consumable}", name: "app_consumables_item_view_with_category")]
    #[Route("/consumables/item/{consumable}", name: "app_consumables_item_view")]
    public function consumable(
        Request $request,
        #[CurrentUser]
        User $user,
        EntityManagerInterface $entityManager,
        ConsumableCategoryRepository $categoryRepository,
        Consumable $consumable,
        ?ConsumableCategory $category = null,
    ): Response {
        $category = $category ?? $consumable->getCategory();

        return $this->consumableHelper($categoryRepository, $category, $consumable);
    }

    #[Route("consumables/toOrder", name: "app_consumables_to_order")]
    #[Route("consumables/toOrder/critical", name: "app_consumables_to_order_critical")]
    public function consumableToOrder(
        Request $request,
        ConsumableRepository $consumableRepository,
    ): Response {
        /** @var "app_consumables_to_order"|"app_consumables_to_order_critical" $currentRoute */
        $currentRoute = $request->attributes->get("_route");
        $consumables = match ($currentRoute) {
            "app_consumables_to_order" => $consumableRepository->findAllWithRequiredOrders(),
            "app_consumables_to_order_critical" => $consumableRepository->findAllWithCriticallyRequiredOrders(),
        };

        return $this->render("parts/consumables/consumable_list.html.twig", [
            "title" => $request->attributes->get("_route") === "app_consumables_to_order_critical" ? "Critical order list" : "Order list",
            "consumables" => $consumables,
        ]);
    }

    /**
     * @param ConsumableCategoryRepository $categoryRepository
     * @param ConsumableCategory|null $category
     * @param Consumable|null $consumable
     * @param ConsumableLot|null $lot
     * @param array<string, mixed> $options
     * @return Response
     */
    private function consumableHelper(
        ConsumableCategoryRepository $categoryRepository,
        ?ConsumableCategory $category = null,
        ?Consumable $consumable = null,
        ?ConsumableLot $lot = null,
        array $options = [],
    ): Response {
        $categoryToolbox = new Toolbox([
            new AddTool(
                $this->generateUrl("app_consumables_category_new"),
                icon: "box",
                enabled: $this->isGranted(ConsumableVoter::NEW, "ConsumableCategory"),
                tooltip: "Add a new consumable category",
                iconStack: "add",
            ),
            new AddTool(
                $this->generateUrl("app_consumables_item_add_to", ["category" => $category?->getId()]),
                icon: "consumable",
                enabled: $this->isGranted(ConsumableVoter::ATTR_ADD_TO, $category),
                tooltip: "Add a new consumable",
                iconStack: "add",
            ),
        ]);

        $consumableToolbox = null;
        if ($consumable) {
            $consumableToolbox = new Toolbox([
                new EditTool(
                    $this->generateUrl("app_consumables_item_edit", ["consumable" => $consumable?->getId()]),
                    icon: "consumable",
                    enabled: $this->isGranted(ConsumableVoter::ATTR_EDIT, $consumable),
                    tooltip: "Edit this consumable",
                    iconStack: "edit",
                ),
                new AddTool(
                    $this->generateUrl("app_consumables_item_add_lot", ["consumable" => $consumable?->getId()]),
                    icon: "lot",
                    enabled: $this->isGranted(ConsumableVoter::ATTR_ADD_TO, $consumable),
                    tooltip: "Add a new lot",
                    iconStack: "add",
                ),
            ]);
        }

        return $this->render("parts/consumables/consumables.html.twig", [
            "categories" => $categoryRepository->findAllWithConsumablesAndLots(),
            "currentCategory" => $category,
            "currentConsumable" => $consumable,
            "currentLot" => $lot,
            "toolbox" => [
                "category" => $categoryToolbox,
                "consumable" => $consumableToolbox,
            ],
            "treeViewService" => ConsumableTreeViewService::class,
            ... $options,
        ]);
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
        ?ConsumableCategory $category = null,
        ?Consumable $consumable = null,
    ): Response {
        if (!$consumable and !$category) {
            throw $this->createNotFoundException("Not found.");
        }

        $route = $request->attributes->get("_route");

        if ($route === "app_consumables_item_add_to") {
            $this->denyAccessUnlessGranted("add_to", $category);
            $newEntity = true;

            if ($category) {
                $title = "Consumable Category :: Add To :: {$category->getLongName()}";
            } else {
                $title = "Consumable Category :: Add Item";
            }

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
        ?Consumable $consumable = null,
        ?ConsumableLot $lot = null,
    ): Response {
        if (!$consumable and !$lot) {
            throw $this->createNotFoundException("Not found.");
        }

        $route = $request->attributes->get("_route");

        if ($route === "app_consumables_item_add_lot") {
            $this->denyAccessUnlessGranted("add_lot", $consumable);
            $newEntity = true;
            $title = "Consumable :: {$consumable->getLongName()} :: Add Lot";
            $lot = $consumable->createLot()
                ->setBoughtBy($user)
                ->setBoughtOn(new DateTime("now"))
                ->setConsumable($consumable)
            ;
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