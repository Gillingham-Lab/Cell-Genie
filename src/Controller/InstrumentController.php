<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\Instrument;
use App\Entity\DoctrineEntity\Log;
use App\Entity\DoctrineEntity\User\User;
use App\Form\Instrument\InstrumentType;
use App\Form\Instrument\LogType;
use App\Genie\Enums\InstrumentRole;
use App\Repository\Instrument\InstrumentRepository;
use App\Repository\Instrument\InstrumentUserRepository;
use App\Service\FileUploader;
use App\Service\InstrumentBookingService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Google\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class InstrumentController extends AbstractController
{
    public function __construct(
        readonly private InstrumentRepository $instrumentRepository,
    ) {

    }

    #[Route("/instruments", name: "app_instruments")]
    public function instruments(
        Security $security,
    ): Response {
        $user = $security->getUser();
        assert($user instanceof User);

        $instruments = $this->instrumentRepository->findAllWithUserRole($user);

        return $this->render("parts/instruments/instruments.html.twig", [
            "instruments" => $instruments,
        ]);
    }

    #[Route("instruments/no/{instrument}", name: "app_instruments_view")]
    public function viewInstrument(
        InstrumentUserRepository $instrumentUserRepository,
        Instrument $instrument,
    ): Response {
        $this->denyAccessUnlessGranted("view", $instrument);

        return $this->render("parts/instruments/instrument.html.twig", [
            "instrument" => $instrument,
            "instrumentUsers" => $instrumentUserRepository->findAllInstrumentUsers($instrument),
        ]);
    }

    #[Route("parts/instruments/view/log/{instrument}", name: "app_instrument_view_log_partial")]
    public function partialViewInstrumentLog(
        Instrument $instrument
    ): Response {
        $this->denyAccessUnlessGranted("view", $instrument);

        return $this->render("parts/instruments/log_part.html.twig", [
            "entity" => $instrument,
            "entityId" => $instrument->getId()->toBase32(),
            "logs" => $instrument->getLogs(),
        ]);
    }

    #[Route("parts/instruments/form/log/{instrument}", name: "app_instrument_form_log_partial")]
    #[Route("parts/instruments/form/log/{instrument}/{log}", name: "app_instrument_form_log_partial")]
    public function partialViewLogForm(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        Instrument $instrument,
        ?Log $log = null,
    ): Response {
        /** @var User $currentUser */
        $currentUser = $security->getUser();

        if (!$log) {
            $this->denyAccessUnlessGranted("log_create", $instrument);

            $entity = new Log();
            $entity->setOwner($currentUser);
            $entity->setGroup($currentUser->getGroup());

            $action = $this->generateUrl($request->get("_route"), ["instrument" => $instrument->getId()]);
        } else {
            $this->denyAccessUnlessGranted("log_edit", [$instrument, $log]);
            $entity = $log;
            $action = $this->generateUrl($request->get("_route"), ["instrument" => $instrument->getId(), "log" => $log->getId()]);
        }

        $form = $this->createForm(LogType::class, $entity, [
            "save_button" => true,
            "action" => $action,
        ]);

        $newForm = clone $form;

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $instrument->addLog($entity);
            $entityManager->flush();

            return $this->render("parts/forms/form_part.html.twig", [
                "form" => $newForm,
            ]);
        }

        return $this->render("parts/forms/form_part.html.twig", [
            "form" => $form,
        ]);
    }

    #[Route("parts/instruments/remove/log/{instrument}/{log}", name: "app_instrument_remove_log_partial")]
    public function partialRemoveLog(
        EntityManagerInterface $entityManager,
        Instrument $instrument,
        Log $log,
    ): Response {
        $this->denyAccessUnlessGranted("log_remove", [$instrument, $log]);

        $entityManager->remove($log);
        $entityManager->flush();

        $this->addFlash("success", "The log entry was removed");

        return $this->redirectToRoute("app_instrument_view_log_partial", ["instrument" => $instrument->getId()]);
    }

    #[Route("/instruments/add", name: "app_instruments_add")]
    public function addInstrument(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
    ) {
        return $this->addOrEditInstruments($request, $entityManager, $fileUploader);
    }

    #[Route("/instruments/edit/{instrument}", name: "app_instruments_edit")]
    public function editInstrument(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        Instrument $instrument,
    ) {
        $this->denyAccessUnlessGranted("edit", $instrument);

        return $this->addOrEditInstruments($request, $entityManager, $fileUploader, $instrument);
    }

    public function addOrEditInstruments(
        Request $request,
        EntityManagerInterface $entityManager,
        FileUploader $fileUploader,
        Instrument $instrument = null,
    ): Response {
        $routeName = $request->attributes->get("_route");
        $currentUser = $this->getUser();
        assert($currentUser instanceof User);
        $new = false;
        $formType = InstrumentType::class;

        if ($routeName === "app_instruments_add") {
            $new = true;

            $instrument = new Instrument();
            $instrument->setUserRole($currentUser, InstrumentRole::Admin);
            $instrument->setOwner($currentUser);
            $instrument->setGroup($currentUser->getGroup());
        }

        $formOptions = [
            "save_button" => true,
        ];

        $form = $this->createForm($formType, $instrument, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $fileUploader->upload($form);
            $fileUploader->updateFileSequence($instrument);

            try {
                $entityManager->persist($instrument);
                $entityManager->flush();

                if ($new) {
                    $message = "Instrument was successfully created.";
                } else {
                    $message = "Instrument was successfully updated.";
                }

                $this->addFlash("success", $message);

                return $this->redirect($this->generateUrl("app_instruments_view", ["instrument" => $instrument->getId()]));
            }
            catch (\Exception $e) {
                if ($new) {
                    $message = "Creating the entry was not possible. Reason: {$e->getMessage()}.";
                } else {
                    $message = "Changing the entry was not possible. Reason: {$e->getMessage()}.";
                }

                $this->addFlash("error", $message);
            }
        }

        return $this->render("parts/forms/add_or_edit_instrument.html.twig", [
            "form" => $form,
            "new" => $new,
            "instrument" => $instrument,
        ]);
    }

    #[Route("/instrument/book/{instrument}", name: "app_instruments_book")]
    public function bookInstrument(
        Request $request,
        Security $security,
        InstrumentBookingService $instrumentBookingService,
        Instrument $instrument,
    ): Response {
        // Get the current user
        $currentUser = $security->getUser();
        assert($currentUser instanceof User);

        if (!$instrument->isBookable()) {
            $this->addFlash("error", "This instrument has not been configured to be booked.");
            return $this->redirectToRoute("app_instruments_view", ["instrument" => $instrument->getId()]);
        }

        $this->denyAccessUnlessGranted("book", $instrument);

        // Calculate the date and time and stuff
        $startTime = new DateTime($request->get("start"), new \DateTimeZone("Europe/Zurich"));
        $endTime = new DateTime($request->get("start"), new \DateTimeZone("Europe/Zurich"));
        $length = (float)($request->get("length"));
        if ($length <= 0) {
            $length = $instrument->getDefaultReservationLength();
        }

        $hours = (int)floor($length);
        $minutes = (int)round(($length - $hours)*60);

        $endTime->add(\DateInterval::createFromDateString("+ {$hours} hours + {$minutes} minutes"));

        try {
            $instrumentBookingService->book($instrument, $currentUser, $startTime, $endTime);

            $this->addFlash("success", "The instrument booking has been set up.");
        } catch (Service\Exception $exception) {
            $this->addFlash("error", "The instrument booking has been set up incorrectly.");
        }


        return $this->redirectToRoute("app_instruments_view", ["instrument" => $instrument->getId()]);
    }
}