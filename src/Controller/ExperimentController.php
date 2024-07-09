<?php

namespace App\Controller;

use App\Entity\DoctrineEntity\Experiment\ExperimentalDesign;
use App\Entity\DoctrineEntity\User\User;
use App\Entity\Experiment;
use App\Entity\ExperimentalRun;
use App\Entity\ExperimentalRunFormEntity;
use App\Entity\ExperimentalRunWell;
use App\Entity\ExperimentalRunWellCollectionFormEntity;
use App\Form\ExperimentalRunType;
use App\Form\ExperimentalRunWellCollectionType;
use App\Genie\Enums\PrivacyLevel;
use App\Repository\ExperimentalRunRepository;
use App\Repository\ExperimentTypeRepository;
use App\Repository\LotRepository;
use App\Repository\Substance\ChemicalRepository;
use App\Repository\Substance\ProteinRepository;
use App\Repository\Substance\SubstanceRepository;
use App\Twig\Components\Live\Experiment\ExperimentalDesignForm;
use App\Twig\Components\Live\Experiment\ExperimentalRunDataForm;
use App\Twig\Components\Live\Experiment\ExperimentalRunForm;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ExperimentController extends AbstractController
{
    public function __construct(
        readonly private ExperimentTypeRepository $experimentTypeRepository,
        readonly private ChemicalRepository $chemicalRepository,
        readonly private ProteinRepository $proteinRepository,
        readonly private SubstanceRepository $substanceRepository,
        readonly private LotRepository $lotRepository,
    ) {
    }

    #[Route('/experiment', name: 'app_experiments')]
    public function index(): Response
    {
        return $this->render('parts/experiments/design_list.html.twig', [
        ]);
    }

    #[Route("/experiment/design/view/{design}", name: "app_experiments_view")]
    #[IsGranted("view", "design")]
    public function viewDesign(
        ExperimentalDesign $design,
    ): Response {
        return $this->render("parts/experiments/design_view.html.twig", [
            "design" => $design,
        ]);
    }

    #[Route("/experiment/design/new", name: 'app_experiments_new')]
    #[IsGranted("ROLE_USER")]
    public function newExperiment(
        #[CurrentUser]
        User $user,
    ): Response {
        return $this->render("parts/forms/component_form.html.twig", [
            "returnTo" => $this->generateUrl("app_experiments"),
            "title" => "Create Experimental Design",
            "subtitle" => "",
            "formComponent" => ExperimentalDesignForm::class,
            "formEntity" => (new ExperimentalDesign())
                ->setOwner($user)
                ->setGroup($user->getGroup())
                ->setPrivacyLevel(PrivacyLevel::Group),
        ]);
    }

    #[Route("/experiment/design/edit/{design}", name: "app_experiments_edit")]
    #[IsGranted("edit", "design")]
    public function editExperiment(
        ExperimentalDesign $design,
    ): Response {
        return $this->render("parts/forms/component_form.html.twig", [
            "returnTo" => $this->generateUrl("app_experiments"),
            "title" => "Edit Experimental Design",
            "subtitle" => "{$design->getNumber()} | {$design->getShortName()}",
            "formComponent" => ExperimentalDesignForm::class,
            "formEntity" => $design,
        ]);
    }

    #[Route("/experiment/design/newRun/{design}", name: "app_experiments_run_new")]
    #[IsGranted("edit", "design")]
    public function newExperimentalRun(
        #[CurrentUser]
        User $user,
        ExperimentalDesign $design,
    ): Response {
        return $this->render("parts/forms/component_form.html.twig", [
            "returnTo" => $this->generateUrl("app_experiments"),
            "title" => "Add experimental Run",
            "subtitle" => "{$design->getNumber()} | {$design->getShortName()}",
            "formComponent" => ExperimentalRunForm::class,
            "formEntity" => (new \App\Entity\DoctrineEntity\Experiment\ExperimentalRun())
                ->setOwner($user)
                ->setGroup($user->getGroup())
                ->setPrivacyLevel(PrivacyLevel::Group)
                ->setScientist($user)
                ->setDesign($design),
            "formComponentData" => [
                "design" => $design,
            ],
        ]);
    }

    #[Route("/experiment/design/addDataToRun/{run}", "app_experiments_run_addData")]
    #[IsGranted("edit", "run")]
    public function addDataToRun(
        \App\Entity\DoctrineEntity\Experiment\ExperimentalRun $run,
    ): Response {
        return $this->render("parts/forms/component_form.html.twig", [
            "returnTo" => $this->generateUrl("app_experiments"),
            "title" => "Add data",
            "subtitle" => "{$run->getDesign()} - {$run->getName()}",
            "no_structure" => true,
            "formComponent" => ExperimentalRunDataForm::class,
            "formEntity" => $run,
            "formComponentData" => [
                "run" => $run,
                "design" => $run->getDesign(),
            ],
        ]);
    }

    ##[Route("/experiment/view/{experiment}", name: "app_experiments_view")]
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
            "substances" => $this->substanceRepository,
            "lots" => $this->lotRepository,
            "chemicals" => $this->chemicalRepository,
            "proteins" => $this->proteinRepository,
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

    #[Route("/experiment/run/{experimentalRun}/clone", name: "app_experiments_clone_run", methods: ["GET", "POST"])]
    public function cloneRun(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        ExperimentalRunRepository $experimentalRunRepository,
        ExperimentalRun $experimentalRun,
    ): Response {
        try {
            $user = $security->getUser();

            $experimentName = $experimentalRun->getName();
            $nameParts = explode(" ", $experimentName);

            if (count($nameParts) > 1 and $nameParts[count($nameParts) - 1] === "(copy)") {
                $copy = array_pop($nameParts);
            } elseif (count($nameParts) > 2 and $nameParts[count($nameParts) - 2] === "(copy") {
                $copy = array_pop($nameParts);
                $copy = array_pop($nameParts);
            }

            $experimentName = implode(" ", $nameParts);

            $clonedExperimentalRun = new ExperimentalRun();
            $clonedExperimentalRun->setName($experimentName . " (copy)");
            $clonedExperimentalRun->setData($experimentalRun->getData());
            $clonedExperimentalRun->setNumberOfWells($experimentalRun->getNumberOfWells());
            $clonedExperimentalRun->setExperiment($experimentalRun->getExperiment());

            if ($user instanceof User) {
                $clonedExperimentalRun->setOwner($user);
            }

            $entityManager->persist($clonedExperimentalRun);

            # Wells
            foreach($experimentalRun->getWells() as $well) {
                $newWell = new ExperimentalRunWell();
                $newWell->setExperimentalRun($clonedExperimentalRun);
                $newWell->setIsExternalStandard($well->isExternalStandard());
                $newWell->setWellData($well->getWellData());
                $newWell->setWellName($well->getWellName());
                $newWell->setWellNumber($well->getWellNumber());

                $clonedExperimentalRun->addWell($newWell);
                $entityManager->persist($newWell);
            }

            $i = 2;
            do {
                if ($i > 10) {
                    throw new \Exception("You've already made 10 copies of this experiment with the same name. Please rename then, I will refuse to continue here.");
                }

                # Try to find entities by name
                $entity = $experimentalRunRepository->findOneBy([
                    "experiment" => $clonedExperimentalRun->getExperiment(),
                    "name" => $clonedExperimentalRun->getName()
                ]);

                if ($entity !== null) {
                    $newName = $experimentName . " (copy {$i})";
                    $clonedExperimentalRun->setName($newName);
                    $i++;
                    continue;
                }

                $entityManager->flush();
                break;
            } while (true);

            $newId = $clonedExperimentalRun->getId();
            $this->addFlash("success", "Experimental run successfully cloned");

            return $this->redirectToRoute("app_experiments_view_run", ["experimentalRun" => $newId]);
        } catch (\Exception $e) {
            $eClass = get_class($e);
            $this->addFlash("error", "Cloning was not possible: {$e->getMessage()}. Contact your administrator ({$eClass}).");
            return $this->redirectToRoute("app_experiments_view_run", ["experimentalRun" => $experimentalRun->getId()]);
        }
    }

    #[Route("/experiment/run/{experimentalRun}/drop", name: "app_experiments_drop_run", methods: ["GET", "POST"])]
    public function dropRun(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        ExperimentalRun $experimentalRun,
    ): Response {
        try {
            $experiment = $experimentalRun->getExperiment();
            $user = $security->getUser();

            if ($user instanceof User and $user !== $experimentalRun->getOwner() and $user->getIsAdmin() === false) {
                $this->addFlash("error", "You can only remove experiments you own, unless you are an admin.");
            } else {
                $entityManager->remove($experimentalRun);
                $entityManager->flush();

                $this->addFlash("success", "Experimental run successfully removed");
            }
        } catch (\Exception $e) {
            $this->addFlash("error", "Removing was not possible: {$e->getMessage()}. Contact your administrator.");
        }

        return $this->redirectToRoute("app_experiments_view", ["experiment" => $experiment->getId()]);
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
            'run' => $experimentalRun,
            'form' => $form,
        ]);
    }
}
