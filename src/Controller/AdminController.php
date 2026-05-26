<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\Site;
use App\Form\CsvType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/admin', name: 'app_admin_')]
final class AdminController extends AbstractController
{
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
}
