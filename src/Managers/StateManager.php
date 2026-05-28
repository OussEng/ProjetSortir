<?php

namespace App\Managers;

use App\Entity\Event;
use App\Enum\State;
use App\Repository\EventRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;
use DateTimeZone;

class StateManager
{


    public function __construct(private EventRepository $eventRepository, private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @throws \Exception
     */
    public function handle(Event $event): void
    {

        $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));

            $end = $event->getDateTimeStart()->add($event->getDuration());

            if ($event->getDateLimitRegistration() < $now) {
                $event->setState(State::CLOSED);
            }

            if ($event->getDateTimeStart() <= $now) {
                $event->setState(State::ON_GOING);
            }

            if ($end <= $now) {
                $event->setState(State::PAST);
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

    }


    /**
     * @throws \Exception
     */
    public function handleAll(): void
    {
        $events = $this->eventRepository->findActive();
        foreach ($events as $event) {
            $this->handle($event);
        }

    }


}
