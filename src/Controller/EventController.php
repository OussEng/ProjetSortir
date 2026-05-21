<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\EventService;
use App\Service\SiteService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;



#[Route('/sortie', name: 'app_')]
final class EventController extends AbstractController
{



    public function __construct(
        private EventService $eventService,
        private SiteService  $siteService,
        private EntityManagerInterface $entityManager,)
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

    #[Route('/cree', name: 'create')]
    public function create(Request $request): Response
    {

        $event = new Event();
        $eventForm = $this->createForm(EventType::class, $event);
        $eventForm->handleRequest($request);


        if($eventForm-> isSubmitted() && $eventForm->isValid()){

            $event = $eventForm->getData();
            $event->setSite($this->getUser()->getSite());
            $event->setOrganiser($this->getUser());


            $this->entityManager->persist($event);
            $this->entityManager->flush();
            $this->addFlash('success', 'La sortie a été ajoutée avec succès');
            return $this->redirectToRoute('app_events');

        }

        return $this->render('event/create.html.twig', [
            'eventForm' => $eventForm,
        ]);
    }

}
