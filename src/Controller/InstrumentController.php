<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\DoctrineEntity\Instrument;
use App\Entity\DoctrineEntity\InstrumentUser;
use App\Entity\User;
use App\Form\Instrument\InstrumentType;
use App\Genie\Enums\InstrumentRole;
use App\Repository\Instrument\InstrumentRepository;
use App\Repository\UserRepository;
use App\Service\InstrumentBookingService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Google\Client;
use Google\Service\Calendar;
use Google\Service;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class InstrumentController extends AbstractController
{
    public function __construct(
        private InstrumentRepository $instrumentRepository,
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
        UserRepository $userRepository,
        Security $security,
        Instrument $instrument,
    ): Response {
        return $this->render("parts/instruments/instrument.html.twig", [
            "instrument" => $instrument,
            "users" => $userRepository->findAllActives(),
        ]);
    }

    #[Route("/instruments/add", name: "app_instruments_add")]
    public function addInstrument(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
    ) {
        return $this->addOrEditInstruments($request, $security, $entityManager);
    }

    #[Route("/instruments/edit/{instrument}", name: "app_instruments_edit")]
    public function editInstrument(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        Instrument $instrument,
    ) {
        $instrumentUsers = $instrument->getUsers()->filter(fn ($e) => $e->getUser() === $security->getUser() and $e->getRole() === InstrumentRole::Admin or $e->getRole() === InstrumentRole::Responsible);

        if ($instrumentUsers->count() > 0 or $security->isGranted("ROLE_ADMIN")) {
            return $this->addOrEditInstruments($request, $security, $entityManager, $instrument);
        } else {
            return $this->createAccessDeniedException("You must be responsible for this machine or an admin to change this instrument.");
        }
    }

    public function addOrEditInstruments(
        Request $request,
        Security $security,
        EntityManagerInterface $entityManager,
        Instrument $instrument = null,
    ): Response {
        $routeName = $request->attributes->get("_route");
        $currentUser = $security->getUser();
        assert($currentUser instanceof User);
        $new = false;
        $formType = InstrumentType::class;

        if ($routeName === "app_instruments_add") {
            $new = true;

            $instrument = new Instrument();
            $instrument->setUserRole($currentUser, InstrumentRole::Admin);
        }

        $formOptions = [
            "save_button" => true,
        ];

        $form = $this->createForm($formType, $instrument, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
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

        return $this->renderForm("parts/forms/add_or_edit_instrument.html.twig", [
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

        $access = false;

        // Check if the user is admin
        if ($security->isGranted("ROLE_ADMIN")) {
            $access = true;
        }

        // If the instrument requires training, but the user is untrained, deny booking
        if ($instrument->getRequiresTraining() and $instrument->getUsers()->filter(fn(InstrumentUser $iu) => $iu->getUser() === $currentUser and $iu->getRole() !== InstrumentRole::Untrained)) {
            $access = true;
        }

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