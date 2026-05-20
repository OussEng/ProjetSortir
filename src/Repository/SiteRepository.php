<?php

namespace App\Repository;

use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Site>
 */
class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

    public function findById(int $id): Site {
        return $query = $this->createQueryBuilder('s')
            ->Select('s')
            ->andWhere('s.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }
}
