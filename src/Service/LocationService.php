<?php

namespace App\Service;

use App\Entity\Location;
use App\Repository\LocationRepository;


class LocationService
{
    public function __construct(
        private LocationRepository $locationRepository,
    )
    {
    }

    public function getOneLocation(int $id)
    {
        return $this->locationRepository->find($id);
    }

    public function create(Location $location): void{
    {
        $this->locationRepository->save($location);

    }

}
    public function getAllLocations(int $page, int $limit): array
    {
       return $this->locationRepository->findAllPaginated($page, $limit);
    }


    public function countLocations(): int
    {
       return $this->locationRepository->countAll();
    }
    }
