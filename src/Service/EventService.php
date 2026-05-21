<?php

namespace App\Service;

use App\Entity\Participant;
use App\Entity\Site;
use App\Repository\EventRepository;
use Symfony\Bundle\SecurityBundle\Security;

class EventService{
    public function __construct(private EventRepository $eventRepository, private Security $security){
    }

    public function getAllEvents(){
        return $this->eventRepository->findAll();
    }

    public function getEvent(int $id){
        return $this->eventRepository->find($id);
    }

    public function getEventBySite(Site $site)
    {
        return $this->eventRepository->findBy([
            'site' => $site
        ]);
    }

    public function participate(int $id)
    {
        $event = $this->getEvent($id);
        $user = $this->security->getUser();
        assert($user instanceof Participant);
        $event -> addParticipant($user);
        $this -> eventRepository -> save($event);

    }

    public function quit(int $id)
    {
        $event = $this->getEvent($id);
        $user = $this->security->getUser();
        assert($user instanceof Participant);
        $event -> removeParticipant($user);
        $this -> eventRepository -> save($event);

    }

}
