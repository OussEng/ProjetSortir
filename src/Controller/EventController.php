<?php

namespace App\Controller;

use App\Entity\Event;
use App\Enum\State;

use App\Form\EventType;
use App\Form\CancelReasonType;
use App\Form\UpdateEventType;

use App\Service\EventService;
use App\Service\PrivateGroupService;
use App\Service\SiteService;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/sorties', name: 'app_')]

final class EventController extends AbstractController {

    public function __construct(
        private EventService           $eventService,
        private SiteService            $siteService,
        private EntityManagerInterface $entityManager,
        private MailerInterface $mailer,
        private PrivateGroupService $privateGroupService,
    )
    {
    }

    #[Route('', name: 'events')]
    public function all(Request $request): Response {
        $sites = $this->siteService->getAllSites();

        $search = $request->query->get('search', '');
        $siteId = $request->query->get('site', '');
        $state = $request->query->get('state', '');

        $dateFrom = $request->query->get('dateFrom');
        $dateTo = $request->query->get('dateTo');

        $includePast = $request->query->getBoolean('includePast');
        $myEvents = $request->query->getBoolean('myEvents');

        $currentPage = $request->query->getInt('page', 1);
        $limitPerPage = 9;

        $eventsData = $this->eventService->getFilteredPaginatedEvents(
            $search,
            $siteId,
            $state,
            $dateFrom,
            $dateTo,
            $includePast,
            $myEvents,
            $this->getUser(),
            $currentPage,
            $limitPerPage
        );

        return $this->render('event/event.html.twig', [
            'events' => $eventsData['results'],
            'sites' => $sites,
            'currentPage' => (int) $currentPage,
            'totalPages' => (int) $eventsData['totalPages']
        ]);
    }

    #[Route('/{id}', name: 'event', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function byId(int $id): Response {

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

    #[Route('/cree', name: 'create')]
    public function create(Request $request): Response
    {

        $now = new DateTime();


        $event = new Event();
        $eventForm = $this->createForm(UpdateEventType::class, $event);
        $eventForm->handleRequest($request);


        if ($eventForm->isSubmitted() && $eventForm->isValid()) {

            $event = $eventForm->getData();
            $action = $request->request->get('action');


            $event->setSite($this->getUser()->getSite());
            $event->setOrganiser($this->getUser());

            $dateTimeStart = new DateTimeImmutable(
                $event->getDateTimeStart()->format('Y-m-d H:i:s'),
                new DateTimeZone('Europe/Paris')
            );

            $dateLimit = new DateTimeImmutable(
                $event->getDateLimitRegistration()->format('Y-m-d H:i:s'),
                new DateTimeZone('Europe/Paris')
            );


            $event->setDateTimeStart($dateTimeStart->setTimezone(new DateTimeZone('UTC')));
            $event->setDateLimitRegistration($dateLimit->setTimezone(new DateTimeZone('UTC')));


            if ($eventForm->get('group')->getData()) {
                $group = ($eventForm->get('group')->getData());
                $pg = $this->privateGroupService->getById($group->getId());
                foreach ($pg->getMembers() as $participant) {
                    $event->addParticipant($participant);
                    $event->setPrivate(true);
                }
                if ($request->getSession()->get('is_mobile')) {
                    throw $this->createAccessDeniedException("Création de sortie interdite sur mobile.");
                }
            }else{
                $event->setPrivate(false);
            }


            if ($action === 'publish') {
                $event->setState(State::OPEN);
            } elseif ($action === 'save') {
                $event->setState(State::CREATED);
            }


            if ($event->getDateTimeStart() < $now) {
                $this->addFlash('danger', 'La date de début doit être dans le futur');
                return $this->redirectToRoute('app_create');
            }

            if ($event->getDateLimitRegistration() < $now) {
                $this->addFlash('danger', "La date limite d'inscription doit être dans le futur");
                return $this->redirectToRoute('app_create');
            }

            if ($event->getDateLimitRegistration() > $event->getDateTimeStart()) {
                $this->addFlash('danger', "La date limite d'inscription doit être avant la date de début");
                return $this->redirectToRoute('app_create');
            }

            $this->eventService->create($event);
            $this->addFlash('success', 'La sortie a été ajoutée avec succès');
            return $this->redirectToRoute('app_events');

        }

        return $this->render('event/create.html.twig', [
            'eventForm' => $eventForm,
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/inscrire/sortie/{id}', name: 'participate', requirements: ['id' => '\d+'])]
    public function participate(int $id): Response
    {

        $event = $this->eventService->getEvent($id);
        $now = new DateTime();

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

        $email = (new TemplatedEmail())
            ->from(new Address('notification@sortir.com', 'Sortir.com'))
            ->to((string) $this->getUser()->getEmail())
            ->subject('Vous êtes inscrit à une sortie')
            ->htmlTemplate('event/email.html.twig')
            ->context([
                'event' => $event,
            ])
        ;

        $this->mailer->send($email);

        return $this->redirectToRoute('app_event', ['id' => $id]);

    }

    #[Route('/desister/{id}', name: 'quit', requirements: ['id' => '\d+'])]
    public function quit(int $id): Response
    {
        $now = new DateTime();
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

    #[Route('/annuler/{id}', name: 'cancel')]
    public function cancel(int $id): Response
    {
        $user = $this->getUser();
        $event = $this->eventService->getEvent($id);

        $isOrganiser = $user === $event->getOrganiser();
        $isAdmin = $this->isGranted('ROLE_ADMIN');

        if (!$isOrganiser && !$isAdmin) {
            $this->addFlash('danger', "Vous n'êtes pas l'organisateur de cette sortie");
            return $this->redirectToRoute('app_event', ['id' => $id]);
        }

        $this->eventService->cancelEvent($id);
        $this->addFlash('success', 'La sortie a été annulée');

        return $this->redirectToRoute('app_event', ['id' => $id]);
    }

    #[Route('/annuler/motif/{id}', name: 'reason', requirements: ['id' => '\d+'])]
    public function reason(Request $request, int $id): Response{

        $event = $this->eventService->getEvent($id);

        $cancelForm = $this->createForm(CancelReasonType:: class);
        $cancelForm->handleRequest($request);

        if($cancelForm->isSubmitted() && $cancelForm->isValid()){
           $reason = $cancelForm->getData()['reason'];
           $this->eventService->cancelEvent($event, $reason);
            $this->addFlash('succes', "La sortie a été annulée");
            return $this->redirectToRoute('app_event', ['id' => $id]);
        }


        return $this->render('event/reason.html.twig', [
            'cancelForm' => $cancelForm,
        ]);
    }

    /**
     * @param Request $request
     * @param int $id
     * @return Response
     * @throws \Exception
     */
    #[Route('/{id}/modifier', name: 'event_update', requirements: ['id' => '\d+'])]
    public function update(Request $request, int $id): Response {

        $now = new DateTime();

        $event = $this->eventService->getEvent($id);

        if (!$event) {
            $this->addFlash('danger', "La sortie est introuvable.");
            return $this->redirectToRoute('app_home');
        }

        if ($event->getOrganiser() !== $this->getUser()) {
            $this->addFlash('danger', "La sortie est introuvable.");
            return $this->redirectToRoute('app_home');
        }

        $eventForm = $this->createForm(UpdateEventType::class, $event);
        $eventForm->handleRequest($request);

        if ($eventForm->isSubmitted() && $eventForm->isValid()) {

            if ($event->getDateTimeStart() < $now) {
                $this->addFlash('danger', 'La date de début doit être dans le futur');
                return $this->redirectToRoute('app_event_update', ['id' => $id]);
            }

            if ($event->getDateLimitRegistration() < $now) {
                $this->addFlash('danger', "La date limite d'inscription doit être dans le futur");
                return $this->redirectToRoute('app_event_update', ['id' => $id]);
            }

            if ($event->getDateLimitRegistration() > $event->getDateTimeStart()) {
                $this->addFlash('danger', "La date limite d'inscription doit être avant la date de début");
                return $this->redirectToRoute('app_event_update', ['id' => $id]);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'La sortie a été modifiée avec succès');
            return $this->redirectToRoute('app_event', ['id' => $event->getId()]);
        }

        return $this->render('event/update.html.twig', [
            'eventForm' => $eventForm->createView(),
            'event' => $event
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/publier/{id}', name: 'publish')]
    public function publish(int $id): Response
    {
        $event = $this->eventService->getEvent($id);
        $now = new DateTime();

        if (!$event) {
            $this->addFlash('danger', "La sortie est introuvable.");
        }

        if ($event->getOrganiser() !== $this->getUser()) {
            $this->addFlash('danger', "Vous n'êtes pas l'organisateur de cette sortie");
            return $this->redirectToRoute('app_event', ['id' => $id]);
        }

        if ($event->getDateTimeStart() < $now) {
            $this->addFlash('danger', 'La date de début doit être dans le futur');
            return $this->redirectToRoute('event_update', ['id' => $id]);
        }

        if ($event->getDateLimitRegistration() < $now) {
            $this->addFlash('danger', "La date limite d'inscription doit être dans le futur");
            return $this->redirectToRoute('event_update', ['id' => $id]);
        }

        if ($event->getDateLimitRegistration() > $event->getDateTimeStart()) {
            $this->addFlash('danger', "La date limite d'inscription doit être avant la date de début");
            return $this->redirectToRoute('event_update', ['id' => $id]);
        }

        $this->eventService->publish($event);
        $this->addFlash('success', 'La sortie a été publiée avec succès');

        return $this->redirectToRoute('app_event', ['id' => $id]);

    }


}
