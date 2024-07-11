<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Form\Experiment\ExperimentalDesignType;
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

#[AsLiveComponent(template: "Components/Form/TabbedForm.html.twig")]
class ExperimentalDesignForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use LiveCollectionTrait;

    #[LiveProp]
    public ?ExperimentalDesign $initialFormData = null;

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
        $success = $this->save();

        if ($success) {
            return $this->redirectToRoute("app_experiments_view", ["design" => $success->getId()]);
        } else {
            throw new \Exception("There was an error with this form.");
        }
    }

    #[LiveAction]
    public function save(): ?ExperimentalDesign
    {
        $this->submitForm();

        try {
            $formEntity = $this->getForm()->getData();

            if ($formEntity->getId() === null) {
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
            ExperimentalDesignType::class,
            $this->initialFormData,
        );
    }
}