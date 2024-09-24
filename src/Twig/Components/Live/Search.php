<?php
declare(strict_types=1);

namespace App\Twig\Components\Live;

use App\Entity\DoctrineEntity\User\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PreMount;

#[AsLiveComponent]
class Search extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use ComponentToolsTrait;

    #[LiveProp]
    public string $formId;

    #[LiveProp]
    public string $formType;

    #[LiveProp]
    public ?array $formData = null;

    #[LiveProp(hydrateWith: "hydrateFormOptions", dehydrateWith: "dehydrateFormOptions")]
    ##[Ignore]
    public array $formOptions = [];

    #[LiveProp]
    public string $title;

    #[LiveProp]
    public ?string $eventSuffix = null;

    public function __construct(
        //#[CurrentUser]
        //private ?User $user,
        private readonly SerializerInterface $serializer,
    ) {

    }

    public function hydrateFormOptions(array $data)
    {
        if (method_exists($this->formType, "deserialize")) {
            return $this->formType::deserialize($this->serializer, $data);
        } else {
            return [];
        }
    }

    public function dehydrateFormOptions(array $data)
    {
        if (method_exists($this->formType, "serialize")) {
            return $this->formType::serialize($this->serializer, $data);
        } else {
            return [];
        }
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(
            $this->formType,
            $this->formData,
            $this->formOptions,
        );
    }

    #[PreMount]
    public function fillRememberedData($properties): array
    {
        return $properties;
    }

    /**
     * Submits the form and emits the search results.
     * @return void
     */
    #[LiveAction]
    public function save()
    {
        $this->submitForm();

        $eventData = [];
        foreach ($this->getForm()->getData() as $field => $value) {
            if (empty($value)) {
                continue;
            }

            if (is_object($value)) {
                if (method_exists($value, "getId")) {
                    $eventData[$field] = $value->getId();
                } elseif (method_exists($value, "getUlid")) {
                    $eventData[$field] = $value->getUlid();
                } elseif ($value instanceof \BackedEnum) {
                    $eventData[$field] = $value->value;
                } else {
                    $eventData[$field] = (string)$value;
                }
            } else {
                $eventData[$field] = $value;
            }
        }

        if ($this->eventSuffix) {
            $this->emitUp("search.{$this->eventSuffix}", $eventData);
        } else {
            $this->emitUp("search", $eventData);
        }
    }

    /**
     * Submits the form and stores the search results for retrieval later.
     * @TODO
     * @return void
     */
    #[LiveAction]
    public function remember()
    {

    }

    #[LiveAction]
    public function reset()
    {
        $this->formData = [];
        $this->resetForm();

        if ($this->eventSuffix) {
            $this->emitUp("search.{$this->eventSuffix}", []);
        } else {
            $this->emitUp("search", []);
        }
    }
}