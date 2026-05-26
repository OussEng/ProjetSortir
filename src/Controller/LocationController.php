<?php

namespace App\Controller;

use App\Entity\Location;
use App\Form\LocationType;
use App\Repository\LocationRepository;
use App\Service\LocationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/lieu', name: 'app_location_')]
final class LocationController extends AbstractController
{
    public function __construct(
        private LocationService $locationService,
    )
    {
    }
    #[Route('/{page}', name: 'list', requirements: ['page' => '\d+'])]
    public function index(LocationRepository $locationRepository, int $page = 1): Response
    {
        $limit = 10;

        $locations = $locationRepository->findAllPaginated($page, $limit);
        $total = $locationRepository->countAll();
        $totalPages = ceil($total / $limit);

        return $this->render('location/index.html.twig', [
            'locations'   => $locations,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'total'       => $total,
        ]);
    }



    #[Route('/cree', name: 'create')]
    public function create(Request $request): Response
    {

        $location = new Location();
        $locationForm = $this->createForm(LocationType::class, $location);
        $locationForm->handleRequest($request);

        if ($locationForm->isSubmitted() && $locationForm->isValid()) {
            $location = $locationForm->getData();

            $this->locationService->create($location);
            $this->addFlash('success', 'Le lieu a été créé avec succès.');
            return $this->redirectToRoute('app_location_list');
        }

        return $this->render('location/create.html.twig', [
            'locationForm' => $locationForm,
        ]);
    }
}
