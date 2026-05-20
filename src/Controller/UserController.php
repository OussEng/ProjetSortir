<?php

namespace App\Controller;

use App\Form\ChangePasswordFormType;
use App\Form\ProfileEditorType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    readonly private EntityManagerInterface $entityManager;
    readonly private UserPasswordHasherInterface $passwordHasher;
    public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher){
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/profil', name: 'app_user')]
    public function index(): Response
    {
        $user = $this->getUser();

        return $this->render('user/profile.html.twig',[
            'user' => $user
        ]);
    }

    #[Route('/profil/modification', name: 'app_user_edit')]
    public function edit(Request $request): Response
    {
        $user = $this->getUser();
        $userForm = $this->createForm(ProfileEditorType::class, $user);

        $userForm->handleRequest($request);

        if($userForm->isSubmitted() && $userForm->isValid()){
            $this->entityManager->persist($user);
            $this->entityManager->flush();
            return $this->redirectToRoute('app_user');
        }

        return $this->render('user/profileEditor.html.twig',[
            'userForm' => $userForm
        ]);

    }

    #[Route('/profil/modification/mot-de-passe', name: 'app_user_edit_password')]
    public function editPassword(Request $request): Response
    {
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
}
