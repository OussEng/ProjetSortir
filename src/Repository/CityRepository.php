<?php

namespace App\Repository;

use App\Entity\City;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<City>
 */
class CityRepository extends ServiceEntityRepository{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, City::class);
    }

    /**
     * @return City Returns City
     */
    public function findById(int $id): City {
        return $query = $this->createQueryBuilder('c')
            ->Select('c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }
}
