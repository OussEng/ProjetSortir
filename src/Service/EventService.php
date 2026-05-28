<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Participant;
use App\Entity\Site;
use App\Enum\State;
use App\Repository\EventRepository;
use Symfony\Bundle\SecurityBundle\Security;

class EventService{
    public function __construct(private readonly EventRepository $eventRepository,
                                private readonly Security        $security){
    }

    public function getAllEvents(): array
    {
        return $this->eventRepository->findAll();
    }

    public function getEvent(int $id): Event|null
    {
        return $this->eventRepository->find($id);
    }

    public function getEventBySite(Site $site): array
    {
        return $this->eventRepository->findBy([
            'site' => $site
        ]);
    }

    public function participate(int $id): void
    {
        $event = $this->getEvent($id);
        $user = $this->security->getUser();
        assert($user instanceof Participant);
        $event -> addParticipant($user);
        $this -> eventRepository -> save($event);

    }

    public function quit(int $id): void
    {
        $event = $this->getEvent($id);
        $user = $this->security->getUser();
        assert($user instanceof Participant);
        $event -> removeParticipant($user);
        $this -> eventRepository -> save($event);

    }

    public function cancelEvent(Event $event, $reason): void
    {
        $event->setState(State::CANCELED);
        $event->setEventDescription($reason);
        $this->eventRepository->save($event);
    }

    public function getEventByOrganiserId(int $id): array{
        return $this->eventRepository->findAllEventByOrganiserId($id);
    }

    public function create(Event $event): void{
            $this->eventRepository->save($event);
    }

    public function getEventsByUserParticipated(Participant $participant, int $page, int $limit): array{
        return $this->eventRepository->findEventsByUserParticipated($participant, $page, $limit);
    }

    public function getFilteredPaginatedEvents(
        string $search,
        ?string $siteId,
        ?string $state,
        ?string $dateFrom,
        ?string $dateTo,
        bool $includePast,
        bool $myEvents,
        ?Participant $participant,
        int $page,
        int $limit ): array{
        return $this->eventRepository->findFilteredPaginated(
            $search,
            $siteId,
            $state,
            $dateFrom,
            $dateTo,
            $includePast,
            $participant,
            $myEvents,
            $page,
            $limit,

        );
    }


    public function publish(Event $event)
    {
        $event->setState(State::OPEN);
        $this->eventRepository->save($event);
    }


}
