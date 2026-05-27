<?php

namespace App\Service;

use App\Entity\City;
use App\Repository\CityRepository;
use Symfony\Component\TypeInfo\Type\ArrayShapeType;

class CityService
{

    public function __construct(
        private CityRepository $cityRepository,
    )
    {
    }
    public function create(City $city): void
    {
        $this->cityRepository->save($city);
    }
    public function getCity(int $id): City
    {
        return $this->cityRepository->findById($id);
    }
}
