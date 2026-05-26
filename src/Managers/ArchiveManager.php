<?php

namespace App\Managers;

use App\Enum\State;
use App\Repository\EventRepository;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;

class ArchiveManager
{


    public function __construct(private EventRepository $eventRepository, private EntityManagerInterface $entityManager)
    {

    }

    /**
     * @throws \Exception
     */
    public function archive(): void
    {

        $events = $this->eventRepository->findPast();
        $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));

        foreach ($events as $event){

            $end = $event->getDateTimeStart()->add($event->getDuration());

            if ($end->add(new DateInterval('P30D')) <= $now){
                $event->setState(State::ARCHIVED);
            }
        }

        $this->entityManager->flush();

    }

}
