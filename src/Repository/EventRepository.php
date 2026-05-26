<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Site;
use App\Enum\State;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository{
    public function __construct(ManagerRegistry $registry){
        parent::__construct($registry, Event::class);
    }


    /**
     * @return Event[] Returns an array of Event objects
    */
    public function findEventBySite(Site $site): array {
        return $query = $this->createQueryBuilder('e')
            ->Select('e')
            ->andWhere('e.site = :site')
            ->setParameter('site', $site->getId())
            ->getQuery()
            ->getResult();
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

    /**
     * @return Event[]
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.state NOT IN (:states)')
            ->setParameter('states', [State::CANCELED, State::ARCHIVED, State::CREATED])
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Event[]
     */
    public function findPast(): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.state IN (:state)')
            ->setParameter('state', [State::PAST])
            ->getQuery()
            ->getResult();
    }
}
