<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ChangePasswordFormType;
use App\Form\ProfileEditorType;
use App\Repository\EventRepository;
use App\Service\EventService;
use App\Service\ParticipantService;
use App\Utils\FileUploader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController {

    public function __construct(private readonly EntityManagerInterface      $entityManager ,
                                private readonly UserPasswordHasherInterface $passwordHasher,
                                private readonly FileUploader                $fileUploader,
                                private readonly ParticipantService          $participantService,
                                private readonly EventService                $eventService){
    }

    #[Route('/profil', name: 'app_user')]
    public function index(): Response
    {
        $user = $this->getUser();
        $events = $this->eventService->getEventByOrganiserId($user->getId());
        $userParticipate = $this->eventService->getEventsByUserParticipated($user);
        return $this->render('user/profile.html.twig',[
            'user' => $user,
            'events' => $events,
            'userParticipate' => $userParticipate
        ]);
    }

    #[Route('/profil/modification', name: 'app_user_edit')]
    public function edit(Request $request, #[Autowire('%img_profil_dir%')] string $imgProfil): Response
    {
        $user = $this->getUser();

        if (!$user instanceof Participant) {
            throw $this->createAccessDeniedException('Vous devez être connecté.');
        }

        $userForm = $this->createForm(ProfileEditorType::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $file = $userForm->get('img')->getData();

            if ($file) {
                $filename = $this->fileUploader->upload(
                    $file,
                    $imgProfil,
                    'img_profil_' . $user->getUsername()
                );

                $user->setImg($filename);
            }

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'Profil mis à jour avec succès.');

            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/profileEditor.html.twig', [
            'userForm' => $userForm,
        ]);
    }

    #[Route('/profil/{username}', name: 'app_user_other_profil')]
    public function otherUserProfile(EventRepository $eventRepository,string $username, int $page = 1): Response {

        $user = $this->participantService->getOneParticipantByUsername($username);
        $events = $this->eventService->getEventByOrganiserId($user->getId());

        $limit = 9;

        $userParticipate = $this->eventService->getEventsByUserParticipated($user, $page, $limit);

        $total = $eventRepository->countAllEventCreated($user);
        $totalPages = ceil($total / $limit);

        return $this->render('user/profile.html.twig',[
            'user' => $user,
            'events' => $events,
            'userParticipate' => $userParticipate,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'total'       => $total

        ]);
    }

    #[Route('/profil/modification/mot-de-passe', name: 'app_user_edit_password')]
    public function editPassword(Request $request): Response {
        $user = $this->getUser();
        $editPasswordForm = $this->createForm(ChangePasswordFormType::class);

        $editPasswordForm->handleRequest($request);

        if($editPasswordForm->isSubmitted() && $editPasswordForm->isValid()){

            $currentPassword = $editPasswordForm->get('currentPassword')->getData();

            if (!$this->passwordHasher->isPasswordValid($user, $currentPassword)){
                $editPasswordForm->get('currentPassword')->addError(new FormError('Mot de passe invalide'));

                return $this->render('user/passwordEditor.html.twig', [
                    'editPasswordForm' => $editPasswordForm
                ]);
            }

            $newPassword = $editPasswordForm->get('newPassword')->getData();

            $hashedPassword = $this->passwordHasher->hashPassword(
                $user,
                $newPassword
            );

            $user->setPassword($hashedPassword);


            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/passwordEditor.html.twig',[
            'editPasswordForm' => $editPasswordForm
        ]);

    }

    #[Route('/profil/sortie/{id}', name: 'app_user_events')]
    public function allSortieUser( int $id, int $page = 1): Response{

        $events = $this->eventService->getEventByOrganiserId($id);

        $limit = 9;

        return $this->render('user/allSortie.html.twig',[
            'events' => $events
        ]);
    }

    #[Route('/profil/{id}/participation/{page<\d+>?1}', name: 'app_user_events_participated')]
    public function allUserParticipated(EventRepository $eventRepository, int $id, int $page = 1): Response{

        $user = $this->participantService->getParticipant($id);

        $limit = 9;

        $events = $this->eventService->getEventsByUserParticipated($user, $page, $limit);
        $total = $eventRepository->countAllEventParticipated($user);
        $totalPages = ceil($total / $limit);


        return $this->render('user/allUserParticipated.html.twig',[
            'events' => $events,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'total'       => $total
        ]);
    }
}
