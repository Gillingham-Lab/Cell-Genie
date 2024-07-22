<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Form\Experiment\ExperimentalRunDataType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;

#[AsLiveComponent(template: "Components/Form/CompartmentForm.html.twig")]
class ExperimentalRunDataForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use LiveCollectionTrait;

    #[LiveProp()]
    public ?ExperimentalRun $initialFormData = null;

    #[LiveProp]
    public ?ExperimentalRun $run = null;

    #[LiveProp]
    public ?ExperimentalDesign $design = null;

    #[LiveProp]
    public string $submitButtonLabel = "Save and return";

    #[LiveProp]
    public string $saveButtonLabel = "Save";

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {

    }

    #[LiveAction]
    public function submit(): Response
    {
        $formEntity = $this->save();
        return $this->redirectToRoute("app_experiments_view", ["design" => $this->design]);
    }

    #[LiveAction]
    public function save(): ?ExperimentalRun
    {
        $this->submitForm();
        $formEntity = $this->getForm()->getData();
        $formEntity->updateTimestamps();

        try {
            $this->entityManager->flush();
            return $formEntity;
        } catch (\Exception $e) {
            $this->addFlash("error", "Saving was not possible: {$e->getMessage()}");
            return null;
        }
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(
            ExperimentalRunDataType::class,
            $this->initialFormData,
            [
                "design" => $this->design,
            ]
        );
    }

    private function getDataModelValue(): ?string
    {
        return 'debounce(1000)|*';
    }
}