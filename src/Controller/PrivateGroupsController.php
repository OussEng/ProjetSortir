<?php

namespace App\Controller;


use App\Entity\PrivateGroup;
use App\Form\PrivateGroupType;
use App\Repository\PrivateGroupRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/groupe', name: 'app_groupe_')]
final class PrivateGroupsController extends AbstractController
{


    public function __construct(private PrivateGroupRepository $repo, private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/liste', name: 'liste')]
    public function liste(): Response
    {
        return $this->render('private_groups/liste.html.twig', [
            'groupes' => $this->repo->findBy(['organiser' => $this->getUser()])
        ]);
    }

    #[Route('/detail/{id}', name: 'detail')]
    public function detail(PrivateGroup $group): Response
    {
        return $this->render('private_groups/detail.html.twig', [
            'groupe' => $group
        ]);
    }

    #[Route('/creer', name: 'create')]
    public function create(Request $request): Response
    {


        $gp = new PrivateGroup();
        $form = $this->createForm( PrivateGroupType::class, $gp);
        $form->handleRequest($request);

        if (!$this->getUser()){
            $form->addError(new FormError("Vous devez être connecté pour créer un groupe privé"));
        }

        if ($form->isSubmitted() && $form->isValid() && $this->getUser()) {

            $gp->setOrganiser($this->getUser());

            $this->entityManager->persist($gp);
            $this->entityManager->flush();

            $this->addFlash("success" , "Le groupe a bien été créé");
            return $this->redirectToRoute('app_groupe_liste');

        }


        return $this->render('private_groups/create.html.twig', [
            'form' => $form
        ]);
    }

    #[Route('/modifier/{id}', name: 'edit')]
    public function edit(Request $request, PrivateGroup $pg): Response
    {
        $form = $this->createForm( PrivateGroupType::class, $pg);
        $form->handleRequest($request);

        if (!$this->getUser()){
            $form->addError(new FormError("Vous devez être connecté pour modifier un groupe privé"));
        }
        if ($request->getSession()->get('is_mobile')) {
            throw $this->createAccessDeniedException("Création de groupe interdite sur mobile.");
        }

        if ($form->isSubmitted() && $form->isValid() && $this->getUser()) {

            $pg->setOrganiser($this->getUser());

            $this->entityManager->persist($pg);
            $this->entityManager->flush();

            $this->addFlash("success" , "Le groupe a bien été créé");
            return $this->redirectToRoute('app_groupe_liste');

        }

        return $this->render('private_groups/create.html.twig', [
            'controller_name' => 'GroupePriveController',
            'form' => $form
        ]);
    }

    #[Route('/supprimer/{id}', name: 'delete')]
    public function delete(PrivateGroup $pg): Response
    {

        if ($this->getUser() === $pg->getOrganiser()) {
            $this->entityManager->remove($pg);
            $this->entityManager->flush();

            $this->addFlash("success" , "Le groupe a bien été supprimé");
            return $this->redirectToRoute('app_groupe_liste');
        }

        return $this->redirectToRoute('app_groupe_liste');
    }

}
