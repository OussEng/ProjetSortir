<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, Event::class);
    }

    public function findAllEventByOrganiserId(int $id): array {
        return $query = $this->createQueryBuilder('e')
            ->Select('e')
            ->andWhere('e.organiser = :id')
            ->setParameter('id', $id)
            ->addOrderBy('e.dateTimeStart', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function save(Event $event): void
    {
        $this->getEntityManager()->persist($event);
        $this->getEntityManager()->flush();
    }

//    public function findFilteredPaginated(
//        string $search,
//        string $siteId,
//        string $state,
//        int $page,
//        int $limit ): array {
//
//        $qb = $this->createQueryBuilder('e')
//            ->join('e.site', 's')
//            ->addSelect('s');
//
//        if ($search) {
//            $qb->andWhere('e.title LIKE :search OR e.eventDescription LIKE :search')
//                ->setParameter('search', '%'.$search.'%');
//        }
//
//        if ($siteId) {
//            $qb->andWhere('s.id = :siteId')
//                ->setParameter('siteId', $siteId);
//        }
//
//        if ($state) {
//            $qb->andWhere('e.state = :state')
//                ->setParameter('state', $state);
//        }
//
//        $offset = ($page - 1) * $limit;
//
//        $qbCount = clone $qb;
//        $qbCount->select('COUNT(e.id)');
//        $total = (int) $qbCount->getQuery()->getSingleScalarResult();
//
//        $results = $qb
//            ->orderBy('e.dateTimeStart', 'ASC')
//            ->setFirstResult($offset)
//            ->setMaxResults($limit)
//            ->getQuery()
//            ->getResult();
//
//        return [
//            'results' => $results,
//            'totalPages' => ceil($total / $limit)
//        ];
//    }
    public function findFilteredPaginated(
        string $search,
        string $siteId,
        string $state,
        int $page,
        int $limit
    ): array {

        $offset = ($page - 1) * $limit;

        $qb = $this->createQueryBuilder('e')
            ->join('e.site', 's')
            ->addSelect('s');

        if (!empty($search)) {
            $qb->andWhere('e.title LIKE :search OR e.eventDescription LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($siteId)) {
            $qb->andWhere('s.id = :siteId')
                ->setParameter('siteId', $siteId);
        }

        if (!empty($state)) {
            $qb->andWhere('e.state = :state')
                ->setParameter('state', $state);
        }

        $countQb = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->join('e.site', 's');

        if (!empty($search)) {
            $countQb->andWhere('e.title LIKE :search OR e.eventDescription LIKE :search')
                ->setParameter('search', '%' . $search . '%');
        }

        if (!empty($siteId)) {
            $countQb->andWhere('s.id = :siteId')
                ->setParameter('siteId', $siteId);
        }

        if (!empty($state)) {
            $countQb->andWhere('e.state = :state')
                ->setParameter('state', $state);
        }

        $total = (int) $countQb->getQuery()->getSingleScalarResult();

        $results = $qb
            ->orderBy('e.dateTimeStart', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return [
            'results' => $results,
            'totalPages' => (int) ceil($total / $limit),
            'totalItems' => $total
        ];
    }
}
