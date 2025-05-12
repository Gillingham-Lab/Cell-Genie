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
    public bool $showEmpty = false;

    #[LiveProp]
    public ?array $initialFormData = null;

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
    public function placeQuickOrder(
        EntityManagerInterface $entityManager,
        #[CurrentUser]
        User $user,
    ): void {
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