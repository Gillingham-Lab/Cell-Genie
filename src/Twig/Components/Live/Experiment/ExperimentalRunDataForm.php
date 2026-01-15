<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalModel;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Form\Experiment\ExperimentalRunDataType;
use App\Service\Experiment\ExperimentalDataService;
use App\Twig\Components\Trait\ResettableSaveFlagTrait;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
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
    use ResettableSaveFlagTrait;
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
        private readonly EntityManagerInterface $entityManager,
        private readonly ExperimentalDataService $experimentalDataService,
    ) {}

    #[LiveAction]
    public function submit(
        Request $request,
    ): Response {
        $formEntity = $this->save($request);
        return $this->redirectToRoute("app_experiments_view", ["design" => $this->design->getId()->toRfc4122()]);
    }

    #[LiveAction]
    public function save(
        Request $request,
    ): ?ExperimentalRun {
        $this->submitForm();
        $form = $this->getForm();
        $formEntity = $this->getForm()->getData();
        $formEntity->updateTimestamps();

        $conditions = $form->get("_conditions")->get("conditions");
        $modelForConditions = [];
        foreach ($conditions as $child) {
            $modelForConditions[$child->get("name")->getData()] = $child->get("models")->getData();
        }

        $this->experimentalDataService->postUpdate($formEntity, $modelForConditions);

        try {
            $this->entityManager->flush();
            $this->saved = true;
            return $formEntity;
        } catch (Exception $e) {
            $this->addFlash("error", "Saving was not possible: {$e->getMessage()}");
            return null;
        }
    }

    /**
     * @return FormInterface<ExperimentalRun>
     */
    protected function instantiateForm(): FormInterface
    {
        $form = $this->createForm(
            ExperimentalRunDataType::class,
            $this->initialFormData,
            [
                "design" => $this->design,
            ],
        );

        $conditionsToModels = [];
        foreach ($this->initialFormData->getConditions() as $condition) {
            $conditionsToModels[$condition->getName()] = array_map(fn(ExperimentalModel $model) => $model->getModel(), $condition->getModels()->toArray());
        }

        $conditions = $form->get("_conditions")->get("conditions");
        foreach ($conditions as $child) {
            if (!$child->has("models")) {
                continue;
            }

            $name = $child->get("name")->getData();
            $child->get("models")->setData($conditionsToModels[$name]);
        }

        return $form;
    }
}
