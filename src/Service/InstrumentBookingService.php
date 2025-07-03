<?php
declare(strict_types=1);

namespace App\Service;

use App\Entity\DoctrineEntity\Instrument;
use App\Entity\DoctrineEntity\User\User;
use DateTime;
use Google\Client;
use Google\Service\Calendar;
use InvalidArgumentException;

class InstrumentBookingService
{
    public function __construct(

    ) {

    }

    public function book(Instrument $instrument, User $user, DateTime $start, DateTime $end): bool
    {
        $authConfig = $instrument->getAuthString();

        if (!$authConfig) {
            throw new InvalidArgumentException("The instrument must have a valid auth config string");
        }

        $authConfig = json_decode($authConfig, true);

        $client = new Client();
        $client->setAuthConfig($authConfig);
        $client->setApplicationName("GIN Instrument Booking");
        $client->setScopes([Calendar::CALENDAR, Calendar::CALENDAR_EVENTS]);

        $calendarService = new Calendar($client);

        // Create start and end time
        $startTime = new Calendar\EventDateTime();
        $startTime->setDateTime($start->format(DateTime::RFC3339));
        $startTime->setTimeZone("Europe/Zurich");

        $endTime = new Calendar\EventDateTime();
        $endTime->setDateTime($end->format(DateTime::RFC3339));
        $startTime->setTimeZone("Europe/Zurich");

        $now = (new DateTime("now"))->format(DateTime::RFC3339);

        // Create event
        $event = new Calendar\Event();
        $event->setSummary($user->getFullName());
        $event->setDescription("automatically booked via GIN ($now)");
        $event->setStart($startTime);
        $event->setEnd($endTime);

        $calendarService->events->insert($instrument->getCalendarId(), $event);

        return true;
    }
}