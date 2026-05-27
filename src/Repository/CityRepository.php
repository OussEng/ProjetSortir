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

    public function save(City $city)
    {
        $this->getEntityManager()->persist($city);
        $this->getEntityManager()->flush();
    }

    public function findAllPaginated(int $page, int $limit): array
    {
        return $this->createQueryBuilder('l')
            ->orderBy('l.id', 'ASC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countAll(): int
    {
        return $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}

