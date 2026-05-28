<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Participant;
use App\Enum\State;
use App\Form\CsvType;
use App\Service\EventService;
use App\Service\ParticipantService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'app_admin_')]
final class AdminController extends AbstractController {

    public function __construct(private readonly ParticipantService $participantService,
                                private readonly EventService       $eventService) {
    }

    #[Route('', name: 'index')]
    public function index(): Response
    {

        return $this->render('admin/index.html.twig', [

        ]);
    }

    #[Route('/upload', name: 'register_csv')]
    public function upload(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $userPasswordHasher): Response
    {

        $form = $this->createForm(CsvType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $csvFile = $form->get('csvFile')->getData();
            $password = $form->get('password')->getData();
            $site = $form->get('site')->getData();


            if ($csvFile && $site) {
                $filePath = $csvFile->getPathname();

                if (($handle = fopen($filePath, 'r')) !== FALSE) {

                    fgetcsv($handle, 1000, ',');


                    while (($data = fgetcsv($handle, 1000, ';')) !== FALSE) {

                        list($pseudo, $nom, $prenom, $email, $telephone) = $data;


                        $user = new Participant();
                        $user->setUsername($pseudo);
                        $user->setLastname($nom);
                        $user->setFirstname($prenom);
                        $user->setEmail($email);
                        $user->setPhone($telephone);


                        $user->setPassword($userPasswordHasher->hashPassword($user, $password));


                        $user->setRoles(['ROLE_USER']);
                        $user->setActive(true);
                        $user->setSite($site);

                        $entityManager->persist($user);
                    }


                    $entityManager->flush();
                    fclose($handle);
                }


                $this->addFlash('success', 'Les participants ont été inscrits avec succès.');


                return $this->redirectToRoute('app_admin_index');
            }
        }

        return $this->render('admin/registerCsv.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/utilisateurs', name: 'users')]
    public function allUsers(Request $request): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $allUsers = $this->participantService->getParticipants();

        $currentPage = $request->query->getInt('page', 1);

        $limitPerPage = 10;

        $usersData = $this->participantService->getPaginatedParticipants($currentPage, $limitPerPage);

        return $this->render('admin/allusers.html.twig', [
            'allUsers' => $allUsers,
            'users'       => $usersData['results'],
            'currentPage' => $currentPage,
            'totalPages'  => $usersData['totalPages']
        ]);
    }

    #[Route('/admin/utilisateurs/supprimer/{id}', name: 'delete_user', methods: ['GET', 'POST'])]
    public function delete(int $id): Response{

        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $this->participantService->deleteUser($id);
        $this->addFlash('success', 'L\'utilisateur a bien été supprimé.');

        return $this->redirectToRoute('app_admin_users');
    }

    #[Route('/admin/utilisateurs/desactiver/{id}', name: 'desactivate_status', methods: ['GET', 'POST'])]
    public function desactivate(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var Event[] $events */
        $events = $this->eventService->getEventByOrganiserId($id);

        if (!empty($events)) {
            foreach ($events as $event) {
                $event->setState(State::ARCHIVED);
            }
        }

        $this->participantService->desactivateUser($id);

        $this->addFlash('success', 'L\'utilisateur a bien été désactivé et ses événements ont été archivés.');

        return $this->redirectToRoute('app_admin_users');
    }


    #[Route('/admin/utilisateurs/activer/{id}', name: 'actived_status', methods: ['GET', 'POST'])]
    public function actived(int $id): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        /** @var Event[] $events */
        $events = $this->eventService->getEventByOrganiserId($id);

        if (!empty($events)) {
            $now = new \DateTime();

            foreach ($events as $event) {

                $eventStart = $event->getDateTimeStart();

                if ($eventStart !== null) {
                    switch (true) {
                        case ($eventStart > $now):
                            $event->setState(State::OPEN);
                            break;

                        case ($eventStart->format('Y-m-d H:i') === $now->format('Y-m-d H:i')):
                            $event->setState(State::ON_GOING);
                            break;

                        case ($eventStart < $now):
                            $event->setState(State::PAST);
                            break;

                        default:
                            $event->setState(State::CLOSED);
                            break;
                    }
                }
            }
        }

        $this->participantService->activedUser($id);

        $this->addFlash('success', 'L\'utilisateur a bien été activé et ses événements mis à jour.');

        return $this->redirectToRoute('app_admin_users');
    }
}
