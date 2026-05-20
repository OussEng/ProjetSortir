<?php

namespace App\Controller;

use App\Entity\Site;
use App\Repository\CityRepository;
use App\Repository\EventRepository;
use App\Repository\SiteRepository;
use App\Service\EventService;
use App\Service\SiteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sorties', name: 'app_')]
final class EventController extends AbstractController
{
    public function __construct(
        private EventService $eventService,
        private SiteService  $siteService)
    {
    }


    #[Route('', name: 'events')]
    public function all(): Response
    {

        $events = $this->eventService->getAllEvents();
        $sites = $this->siteService->getAllSites();

        return $this->render('event/event.html.twig', [
            'events' => $events,
            'sites' => $sites,
        ]);
    }

    #[Route('/{id}', name: 'event', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function byId(int $id): Response
    {
        $event = $this->eventService->getEvent($id);

        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $sites = $this->siteService->getAllSites();

        return $this->render('event/detail.html.twig', [
            'event' => $event,
            'sites' => $sites,
        ]);
    }

    #[Route('/site/{site}', name: 'events_site')]
    public function allBySite(string $site): Response
    {
        $siteEntity = $this->siteService->getByName($site);

        if (!$siteEntity) {
            throw $this->createNotFoundException('Site not found');
        }

        $events = $this->eventService->getEventBySite($siteEntity);
        $sites = $this->siteService->getAllSites();

        return $this->render('event/eventBySite.html.twig', [
            'eventsBySite' => $events,
            'sites' => $sites,
        ]);
    }

}
