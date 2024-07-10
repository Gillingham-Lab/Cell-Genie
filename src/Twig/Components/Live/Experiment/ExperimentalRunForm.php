<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\Experiment\ExperimentalRun;
use App\Form\Experiment\ExperimentalRunType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(template: "Components/Form/TabbedForm.html.twig")]
class ExperimentalRunForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?ExperimentalRun $initialFormData = null;

    #[LiveProp]
    public ?ExperimentalDesign $design = null;

    #[LiveProp]
    public string $submitButtonLabel = "Save and continue";

    #[LiveProp]
    public ?string $onSubmitRedirectTo = null;

    #[LiveProp]
    public string $saveButtonLabel = "Save";

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {

    }

    #[LiveAction]
    public function submit(): Response
    {
        $success = $this->save();

        if ($success) {
            if ($this->onSubmitRedirectTo) {
                return $this->redirect($this->onSubmitRedirectTo);
            } else {
                return $this->redirectToRoute("app_experiments_run_addData", ["run" => $success->getId()]);
            }
        } else {
            throw new \Exception("There was an error with this form.");
        }
    }

    #[LiveAction]
    public function save(): ?ExperimentalRun
    {
        $this->submitForm();

        try {
            $formEntity = $this->getForm()->getData();

            if ($formEntity->getId() === null) {
                if ($this->design) {
                    $formEntity->setDesign($this->design);
                }

                $this->entityManager->persist($formEntity);
            }

            $this->entityManager->flush();

            $this->addFlash("success", "Saved");

            return $formEntity;
        } catch (\Exception $e) {
            $this->addFlash("error", "Failed to save properly due to an error: {$e->getMessage()}.");
            return null;
        }
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(
            ExperimentalRunType::class,
            $this->initialFormData,
        );
    }
}