<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/sortie', name: 'app_event_')]
final class EventController extends AbstractController
{
    private EventRepository $eventRepository;
    private EntityManagerInterface $entityManager;

    /**
     * @param EventRepository $eventRepository
     * @param EntityManagerInterface $entityManager
     */

    public function __construct(EventRepository $eventRepository, EntityManagerInterface $entityManager)
    {
        $this->eventRepository = $eventRepository;
        $this->entityManager = $entityManager;
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
