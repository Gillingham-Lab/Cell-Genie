<?php
declare(strict_types=1);

namespace App\Twig\Components\StockKeeping;

use App\Entity\DoctrineEntity\StockManagement\Consumable;
use App\Entity\DoctrineEntity\StockManagement\ConsumableLot;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Embeddable\Price;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Tool;
use App\Entity\Toolbox\Toolbox as ToolboxEntity;
use App\Form\StockKeeping\QuickOrderType;
use App\Genie\Enums\Availability;
use DateTime;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Error;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ValidatableComponentTrait;

#[AsLiveComponent]
class ConsumableLotView extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use ValidatableComponentTrait;

    #[LiveProp]
    public Consumable $consumable;

    #[LiveProp(writable: true, url: true)]
    public ?ConsumableLot $selectedLot = null;

    #[LiveProp(writable: true, url: true)]
    public bool $showEmpty = false;

    #[LiveProp]
    public ?array $initialFormData = null;

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private Security $security,
    ) {
    }

    #[LiveAction]
    public function toggleShowEmpty(): void
    {
        $this->showEmpty = !$this->showEmpty;
    }

    /**
     * @return iterable<ConsumableLot>
     */
    public function lots(): iterable
    {
        foreach ($this->consumable->getLots() as $lot) {
            if ($this->showEmpty === false and $lot->getAvailability() === Availability::Empty) {
                continue;
            }

            yield $lot;
        }
    }

    #[LiveAction]
    public function viewLot(
        #[LiveArg]
        ConsumableLot $lot
    ): void {
        $this->selectedLot = $lot;
    }

    #[LiveAction]
    public function makeLotArrive(
        EntityManagerInterface $entityManager,
        FlashBagAwareSessionInterface $flashBag,
        #[LiveArg]
        ConsumableLot $lot,
    ): void {
        $lot->setAvailability(Availability::Available);
        $lot->setArrivedOn(new DateTime("now"));

        $entityManager->flush();
        $flashBag->getFlashBag()->add("info", "Lot {$lot->getLotIdentifier()} has been made available.");
    }

    #[LiveAction]
    public function consumeLot(
        EntityManagerInterface $entityManager,
        FlashBagAwareSessionInterface $flashBag,
        #[LiveArg]
        ConsumableLot $lot,
    ): void {
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
            $flashBag->getFlashBag()->add("success", "Consumption complete." . ($isNowEmpty ? " The lot is now empty." : ""));
        } catch (DBALException $e) {
            $flashBag->getFlashBag()->add("error", "Consumption was not possible due to a database error.");
        } catch (Exception $e) {
            $flashBag->getFlashBag()->add("error", $e->getMessage());
        }
    }

    #[LiveAction]
    public function trashLot(
        EntityManagerInterface $entityManager,
        FlashBagAwareSessionInterface $flashBag,
        #[LiveArg]
        ConsumableLot $lot,
    ): void {
        if ($lot->getAvailability() == Availability::Empty) {
            $flashBag->getFlashBag()->add("error", "Cannot make lot {$lot->getLotIdentifier()} available as it is already empty");
        } else {
            try {
                $lot->setAvailability(Availability::Empty);

                if ($lot->getConsumable()->isConsumePackage()) {
                    $lot->setUnitsConsumed($lot->getNumberOfUnits());
                } else {
                    $lot->setPiecesConsumed($lot->getTotalAmountOfPieces());
                }

                $entityManager->flush();
                $flashBag->getFlashBag()->add("info", "Lot {$lot->getLotIdentifier()} has been trashed.");
            } catch (Exception $e) {
                $flashBag->getFlashBag()->add("error", $e->getMessage());
            }
        }
    }

    public function lotTools(ConsumableLot $lot): ToolboxEntity
    {
        $isEmpty = $lot->getAvailability() === Availability::Empty;

        return new ToolboxEntity([
            new Tool(
                path: "",
                icon: "lot",
                enabled: $this->security->isGranted("view", $lot) and !$isEmpty,
                tooltip: "View details",
                iconStack: "view",
                otherAttributes: [
                    "data-action" => "live#action",
                    "data-live-action-param" => "viewLot",
                    "data-live-lot-param" => $lot->getId()->toRfc4122(),
                ],
            ),
            new Tool(
                path: "",
                icon: "lot",
                enabled: $this->security->isGranted("edit", $lot) and !$isEmpty,
                tooltip: "Consume from lot",
                iconStack: "minus",
                otherAttributes: [
                    "data-action" => "live#action",
                    "data-live-action-param" => "consumeLot",
                    "data-live-lot-param" => $lot->getId()->toRfc4122(),
                ],
            ),
            new EditTool(
                path: $this->urlGenerator->generate("app_consumables_lot_edit", ["lot" => $lot->getId()->toRfc4122()]),
                icon: "lot",
                enabled: ($this->security->isGranted("edit", $lot) and !$isEmpty) or $this->security->isGranted("ROLE_GROUP_ADMIN"),
                tooltip: "Edit lot",
                iconStack: "edit",
            ),
            new Tool(
                path: "",
                icon: "arrive",
                enabled: $lot->getAvailability() === Availability::Ordered or $lot->getAvailability() === Availability::InPreparation,
                tooltip: "Make available",
                otherAttributes: [
                    "data-action" => "live#action",
                    "data-live-action-param" => "makeLotArrive",
                    "data-live-lot-param" => $lot->getId()->toRfc4122(),
                ],
            ),
            new Tool(
                path: "",
                icon: "lot",
                enabled: $this->security->isGranted("trash", $lot) and !$isEmpty,
                tooltip: "Trash lot",
                iconStack: "trash",
                otherAttributes: [
                    "data-action" => "live#action",
                    "data-live-action-param" => "trashLot",
                    "data-live-lot-param" => $lot->getId()->toRfc4122(),
                ]
            )
        ]);
    }

    #[LiveAction]
    public function placeQuickOrder(
        EntityManagerInterface $entityManager,
        #[CurrentUser]
        User $user,
    ) {
        $this->submitForm();

        $data = $this->getForm()->getData();
        $lotCount = $this->quickOrder($entityManager, $user, $this->consumable, $data);

        try {
            $entityManager->flush();
            $this->addFlash("success", "Successfully created {$lotCount}");

            $this->resetForm();
        } catch (Error $e) {
            $this->addFlash("error", "An error occured while creating the lots: {$e->getMessage()}");
        }
    }

    protected function instantiateForm(): FormInterface
    {
        if ($this->initialFormData === null) {
            $formData = [
                "times" => 1,
                "numberOfUnits" => $this->consumable->getNumberOfUnits(),
                "unitSize" => $this->consumable->getUnitSize(),
                "priceValue" => $this->consumable->getPricePerPackage()?->getPriceValue(),
                "priceCurrency" => $this->consumable->getPricePerPackage()?->getPriceCurrency() ?? "CHF",
                "status" => Availability::Ordered,
                "location" => $this->consumable->getLocation(),
            ];
        } else {
            $formData = $this->initialFormData;
        }

        return $this->createForm(QuickOrderType::class, $formData);
    }

    private function quickOrder(
        EntityManagerInterface $entityManager,
        User $user,
        Consumable $consumable,
        mixed $data,
    ): int {
        $lotCount = 0;

        $existingLotNames = [];
        foreach ($consumable->getLots() as $lot) {
            $existingLotNames[$lot->getLotIdentifier()] = true;
        }

        $lotIdentifier = $data["lotIdentifier"] ?? date("ymd");

        if (isset($existingLotNames[$lotIdentifier])) {
            $lotIdentifier = $lotIdentifier . "." . date("Hmi");
        }

        for ($i = 0; $i < $data["times"]; $i++) {
            $lot = $consumable->createLot();
            $lot->setNumberOfUnits($data["numberOfUnits"]);
            $lot->setUnitSize($data["unitSize"]);
            $lot->setPricePerPackage(
                (new Price())
                    ->setPriceValue($data["priceValue"])
                    ->setPriceCurrency($data["priceCurrency"])
            );
            $lot->setBoughtBy($user);
            $lot->setAvailability($data["status"]);
            $lot->setBoughtOn(new DateTime("now"));

            if ($data["location"]) {
                $lot->setLocation($data["location"]);
            }

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
}