<?php
declare(strict_types=1);

namespace App\Twig\Components\Live\Experiment;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Form\Experiment\ExperimentalDesignType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\LiveCollectionTrait;

#[AsLiveComponent]
class ExperimentalDesignForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;
    use LiveCollectionTrait;

    #[LiveProp]
    public ?ExperimentalDesign $initialFormData = null;

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {

    }

    #[LiveAction]
    public function saveAndReturn()
    {
        $success = $this->save();

        if ($success) {
            // Jump to Experimental Design
            return $this->redirectToRoute("app_experiments");
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