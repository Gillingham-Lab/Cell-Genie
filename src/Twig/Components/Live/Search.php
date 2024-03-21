<?php
declare(strict_types=1);

namespace App\Twig\Components\Live;

use App\Entity\DoctrineEntity\User\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
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

    #[LiveProp]
    public string $title;

    public function __construct(
        #[CurrentUser]
        private ?User $user,
    ) {

    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(
            $this->formType,
            $this->formData,
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
                } else {
                    $eventData[$field] = (string)$value;
                }
            } else {
                $eventData[$field] = $value;
            }
        }

        $this->emitUp("search", $eventData);
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
        $this->emitUp("search", []);
    }
}