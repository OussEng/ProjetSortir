<?php

namespace App\Controller;

use App\Entity\City;
use App\Form\CityType;
use App\Repository\CityRepository;
use App\Repository\LocationRepository;
use App\Service\CityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/ville', name: 'app_city_')]
final class CityController extends AbstractController
{
    public function __construct(
        private readonly CityService    $cityService,
        private readonly CityRepository $cityRepository,
    )
    {
    }
    #[Route('/{page}', name: 'list', requirements: ['page' => '\d+'])]
    public function index( int $page = 1): Response
    {
        $limit = 9;

        $city = $this->cityRepository->findAllPaginated($page, $limit);
        $total = $this->cityRepository->countAll();
        $totalPages = ceil($total / $limit);

        return $this->render('city/listCity.html.twig', [
            'city'   => $city,
            'currentPage' => $page,
            'totalPages'  => $totalPages,
            'total'       => $total,
        ]);
    }

    #[Route('/cree', name: 'create')]
    public function create(Request $request): Response
    {
        $city = new City();
        $cityForm = $this->createForm(CityType::class, $city);
        $cityForm->handleRequest($request);

        if ($cityForm->isSubmitted() && $cityForm->isValid()) {
            $existingCity = $this->cityRepository->findOneBy([
                'name' => $city->getName()
            ]);

            if ($existingCity) {
                $this->addFlash('danger', 'La ville existe déjà.');

            } else {

                $this->cityService->create($city);
                $this->addFlash('success', 'La ville a été ajoutée.');

                return $this->redirectToRoute('app_city_list');
            }
        }

        return $this->render('city/createCity.html.twig', [
            'cityForm' => $cityForm->createView(),
        ]);
    }
}
