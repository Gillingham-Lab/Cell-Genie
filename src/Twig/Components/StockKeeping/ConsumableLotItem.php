<?php
declare(strict_types=1);

namespace App\Twig\Components\StockKeeping;

use App\Entity\DoctrineEntity\StockManagement\ConsumableLot;
use App\Entity\Toolbox\EditTool;
use App\Entity\Toolbox\Tool;
use App\Entity\Toolbox\Toolbox;
use App\Entity\Toolbox\Toolbox as ToolboxEntity;
use App\Genie\Enums\Availability;
use DateTime;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class ConsumableLotItem extends AbstractController
{
    use DefaultActionTrait;

    #[LiveProp]
    public ConsumableLot $lot;

    #[LiveProp]
    public ?Toolbox $toolbox = null;

    #[LiveProp]
    public bool $expanded = false;

    #[LiveProp]
    public bool $showConsumableName = false;

    #[LiveProp]
    public bool $showConsumableImage = false;

    public function getLotClass(): string
    {
        return match ($this->lot->getAvailability()) {
            Availability::Ordered => "bg-warning-subtle",
            Availability::Empty => "bg-secondary-subtle",
            default => "",
        };
    }

    #[LiveAction]
    public function viewLot(): void
    {
        $this->expanded = !$this->expanded;
    }

    #[LiveAction]
    public function makeLotArrive(
        EntityManagerInterface $entityManager,
        FlashBagAwareSessionInterface $flashBag,
    ): void {
        $this->lot->setAvailability(Availability::Available);
        $this->lot->setArrivedOn(new DateTime("now"));

        $entityManager->flush();
        $flashBag->getFlashBag()->add("info", "Lot {$this->lot->getLotIdentifier()} has been made available.");
    }

    #[LiveAction]
    public function consumeLot(
        EntityManagerInterface $entityManager,
        FlashBagAwareSessionInterface $flashBag,
    ): void {
        $consumable = $this->lot->getConsumable();
        $isNowEmpty = false;

        try {
            // If the package is pristine, we also set the opened date
            // We should do this up here because the code throws an exception if consumption is not possible, and
            // having this further down would require to duplicate this line.
            if ($this->lot->isPristine()) {
                $this->lot->setOpenedOn(new DateTime("now"));
            }

            if ($consumable->isConsumePackage()) {
                if ($this->lot->getUnitsConsumed() >= $this->lot->getNumberOfUnits()) {
                    throw new Exception("There are no packages left.");
                }

                $this->lot->consumeUnit(1);

                if ($this->lot->getUnitsConsumed() == $this->lot->getNumberOfUnits()) {
                    $this->lot->setAvailability(Availability::Empty);
                    $isNowEmpty = true;
                }
            } else {
                if ($this->lot->getTotalAvailablePieces() <= 0) {
                    throw new Exception("There are no pieces left to consume.");
                }

                $this->lot->consumePiece(1);

                if ($this->lot->getTotalAvailablePieces() == 0) {
                    $this->lot->setAvailability(Availability::Empty);
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
    ): void {
        if ($this->lot->getAvailability() == Availability::Empty) {
            $flashBag->getFlashBag()->add("error", "Cannot make lot {$this->lot->getLotIdentifier()} available as it is already empty");
        } else {
            try {
                $this->lot->setAvailability(Availability::Empty);

                if ($this->lot->getConsumable()->isConsumePackage()) {
                    $this->lot->setUnitsConsumed($this->lot->getNumberOfUnits());
                } else {
                    $this->lot->setPiecesConsumed($this->lot->getTotalAmountOfPieces());
                }

                $entityManager->flush();
                $flashBag->getFlashBag()->add("info", "Lot {$this->lot->getLotIdentifier()} has been trashed.");
            } catch (Exception $e) {
                $flashBag->getFlashBag()->add("error", $e->getMessage());
            }
        }
    }

    public function lotTools(): ToolboxEntity
    {
        $lot = $this->lot;
        $isEmpty = $lot->getAvailability() === Availability::Empty;

        return new ToolboxEntity([
            new Tool(
                path: "",
                icon: "lot",
                enabled: $this->isGranted("view", $lot) and !$isEmpty,
                tooltip: $this->expanded ? "Hide details" : "View details",
                iconStack: $this->expanded ? "hidden" : "view",
                otherAttributes: [
                    "data-action" => "live#action",
                    "data-live-action-param" => "viewLot",
                ],
            ),
            new Tool(
                path: "",
                icon: "lot",
                enabled: $this->isGranted("edit", $lot) and !$isEmpty,
                tooltip: "Consume from lot",
                iconStack: "minus",
                otherAttributes: [
                    "data-action" => "live#action",
                    "data-live-action-param" => "consumeLot",
                ],
            ),
            new EditTool(
                path: $this->generateUrl("app_consumables_lot_edit", ["lot" => $lot->getId()->toRfc4122()]),
                icon: "lot",
                enabled: ($this->isGranted("edit", $lot) and !$isEmpty) or $this->isGranted("ROLE_GROUP_ADMIN"),
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
                ],
            ),
            new Tool(
                path: "",
                icon: "lot",
                enabled: $this->isGranted("trash", $lot) and !$isEmpty,
                tooltip: "Trash lot",
                iconStack: "trash",
                otherAttributes: [
                    "data-action" => "live#action",
                    "data-live-action-param" => "trashLot",
                ],
            ),
        ]);
    }
}
