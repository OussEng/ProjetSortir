<?php

namespace App\Service;

use App\Entity\Site;
use App\Repository\EventRepository;

class EventService{
    public function __construct(private EventRepository $eventRepository){
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

    public function getEventByOrganiserId(int $id){
        return $this->eventRepository->findAllEventByOrganiserId($id);
    }



}
