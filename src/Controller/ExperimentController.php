<?php

namespace App\Controller;

use App\Entity\Experiment;
use App\Entity\ExperimentalCondition;
use App\Entity\ExperimentalRun;
use App\Entity\ExperimentalRunFormEntity;
use App\Entity\ExperimentalRunWellCollectionFormEntity;
use App\Entity\InputType;
use App\Entity\User;
use App\Form\ExperimentalRunType;
use App\Form\ExperimentalRunWellCollectionType;
use App\Form\ExperimentalRunWellType;
use App\Repository\ExperimentTypeRepository;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class ExperimentController extends AbstractController
{
    public function __construct(
        private ExperimentTypeRepository $experimentTypeRepository,
    ) {
    }

    #[Route('/experiment', name: 'app_experiments')]
    public function index(): Response
    {
        $experimentTypes = $this->experimentTypeRepository->findAll();

        return $this->render('parts/experiments/experiments.html.twig', [
            'controller_name' => 'ExperimentController',
            'experiment_types' => $experimentTypes,
        ]);
    }

    #[Route("/experiment/view/{experiment}", name: "app_experiments_view")]
    public function viewExperiment(
        Experiment $experiment,
    ): Response {
        return $this->render("parts/experiments/experiment.html.twig", [
            "controller_name" => "ExperimentController",
            "experiment" => $experiment,
        ]);
    }

    #[Route("/experiment/run/{experimentalRun}", name: "app_experiments_view_run", methods: ["GET"])]
    public function viewRun(
        Request $request,
        ExperimentalRun $experimentalRun,
    ): Response {
        return $this->render("parts/experiments/experiments_view_run.html.twig", [
            "controller_name" => "ExperimentController",
            "run" => $experimentalRun,
            "experiment" => $experimentalRun->getExperiment(),
        ]);
    }

    #[Route("/experiment/run/{experimentalRun}/edit-wells", name: "app_experiments_edit_run_wells", methods: ["GET", "POST"])]
    public function editRunWells(
        Request $request,
        EntityManagerInterface $entityManager,
        ExperimentalRun $experimentalRun,
    ): Response {
        $experiment = $experimentalRun->getExperiment();

        $formEntity = new ExperimentalRunWellCollectionFormEntity($experiment);
        $formEntity->updateFromEntity($experimentalRun);

        $form = $this->createForm(ExperimentalRunWellCollectionType::class, $formEntity, [
            "experiment" => $experiment,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            try {
                $formEntity->getUpdatedEntity($experimentalRun);
                $experimentalRun->setModifiedAt(new DateTime("now"));

                $entityManager->flush();

                return $this->redirectToRoute("app_experiments_view_run", ["experimentalRun" => $experimentalRun->getId()]);
            } catch (\Exception $e) {
                $this->addFlash("error", "Creating the experimental run failed: {$e}");
            }
        }

        return $this->renderForm("parts/experiments/experiments_edit_run_wells.html.twig", [
            "controller_name" => "ExperimentController",
            "run" => $experimentalRun,
            "experiment" => $experimentalRun->getExperiment(),
            "form" => $form,
        ]);
    }

    #[Route("/experiment/{experiment}/run/new", name: 'app_experiments_new_run', defaults: ["experimentalRun" => ""], methods: ["GET", "POST"])]
    #[Route("/experiment/{experiment}/run/edit/{experimentalRun}", name: 'app_experiments_edit_run', defaults: ["experimentalRun" => ""], methods: ["GET", "POST"])]
    public function newRun(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        Experiment $experiment,
        ?ExperimentalRun $experimentalRun = null,
    ): Response {
        $formEntity = new ExperimentalRunFormEntity($experiment);

        if ($experimentalRun) {
            $formEntity->updateFromEntity($experimentalRun);
        }

        $form = $this->createForm(ExperimentalRunType::class, $formEntity, [
            "experiment" => $experiment,
            "disable_numberOfWells" => !($experimentalRun === null),
            "save_button" => true,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            try {
                $databaseEntity = $formEntity->getUpdatedEntity();

                $user = $security->getUser();

                if ($user instanceof User) {
                    $databaseEntity->setOwner($user);
                }

                $entityManager->persist($databaseEntity);
                $entityManager->flush();

                if ($experimentalRun) {
                    $this->addFlash("success", "The experimental run was successfully created.");
                } else {
                    $this->addFlash("success", "The experimental run was successfully changed.");
                }

                return $this->redirectToRoute("app_experiments_view_run", ["experimentalRun" => $databaseEntity->getId()]);
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash("error", "The run name is already in use for this experiment type.");
            } catch (\Exception $e) {
                if ($experimentalRun) {
                    $this->addFlash("error", "Changing the experimental run failed: {$e}");
                } else {
                    $this->addFlash("error", "Creating the experimental run failed: {$e}");
                }
            }
        }

        return $this->renderForm('parts/experiments/experiments_new_run.html.twig', [
            'controller_name' => 'ExperimentController',
            'experiment' => $experiment,
            'form' => $form,
        ]);
    }
}
