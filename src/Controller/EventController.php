<?php

namespace App\Controller;

use App\Entity\Event;
use App\Enum\State;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Service\EventService;
use App\Service\SiteService;
use DateTime;
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
        private EventService           $eventService,
        private SiteService            $siteService,
        private EntityManagerInterface $entityManager,
    )
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

        $now = new DateTime('Europe/Paris');


        $event = new Event();
        $eventForm = $this->createForm(EventType::class, $event);
        $eventForm->handleRequest($request);


        if ($eventForm->isSubmitted() && $eventForm->isValid()) {

            $event = $eventForm->getData();
            $event->setSite($this->getUser()->getSite());
            $event->setOrganiser($this->getUser());

            if($event->getDateTimeStart() < $now){
                $this->addFlash('danger', 'La date de début doit être dans le futur');
                return $this->redirectToRoute('app_create');
            }

            if($event->getDateLimitRegistration() < $now){
                $this->addFlash('danger', "La date limite d'inscription doit être dans le futur");
                return $this->redirectToRoute('app_create');
            }

            if($event->getDateLimitRegistration() > $event->getDateTimeStart()){
                $this->addFlash('danger', "La date limite d'inscription doit être avant la date de début");
                return $this->redirectToRoute('app_create');
            }



            $this->entityManager->persist($event);
            $this->entityManager->flush();
            $this->addFlash('success', 'La sortie a été ajoutée avec succès');
            return $this->redirectToRoute('app_events');

        }


        return $this->render('event/create.html.twig', [
            'eventForm' => $eventForm,
        ]);
    }

    #[Route('/inscrire/sortie/{id}', name: 'participate')]
    public function participate(int $id): Response
    {

        $event = $this->eventService->getEvent($id);
        $now = new DateTime('Europe/Paris');

        if ($event->getState() != State::OPEN) {

            $this->addFlash('danger', "La sortie est " . $event->getState()->value);
            return $this->redirectToRoute('app_event', ['id' => $id]);

        }
        if ($event->getNbParticipants() <= count($event->getParticipants())) {

            $this->addFlash('danger', "La sortie est complet");
            return $this->redirectToRoute('app_event', ['id' => $id]);
        }

        if ($event->getDateLimitRegistration() < $now) {

            $this->addFlash('danger', "La date est limite d'inscription est dépassée");
            return $this->redirectToRoute('app_event', ['id' => $id]);
        }


        $this->eventService->participate($id);
        $this->addFlash('success', 'Vous êtes inscrit à cette sortie');

        return $this->redirectToRoute('app_event', ['id' => $id]);

    }

    #[Route('/desister/{id}', name: 'quit')]
    public function quit(int $id): Response
    {
        $now = new DateTime('Europe/Paris');
        $event = $this->eventService->getEvent($id);

        if ($event->getState() != State::OPEN) {

            $this->addFlash('danger', "La sortie est " . $event->getState()->value);
            return $this->redirectToRoute('app_event', ['id' => $id]);
        }

        if ($event->getDateTimeStart() < $now) {

            $this->addFlash('danger', "La date est limite de se desister est terminée");
            return $this->redirectToRoute('app_event', ['id' => $id]);
        }

        $this->eventService->quit($id);
        $this->addFlash('success', 'Vous êtes déinscrit de cette sortie');
        return $this->redirectToRoute('app_event', ['id' => $id]);

    }
}
