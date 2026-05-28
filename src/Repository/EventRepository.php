<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Participant;
use App\Entity\Site;
use App\Enum\State;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findAllEventByOrganiserId(int $id): array
    {
        return $this->createQueryBuilder('e')
            ->select('e')
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

    public function findFilteredPaginated(
        string $search,
        string $siteId,
        string $state,
        ?string $dateFrom,
        ?string $dateTo,
        bool $includePast,
        ?Participant $participant,
        bool $myEvents,
        int $page,
        int $limit
    ): array {

        $offset = ($page - 1) * $limit;

        $qb = $this->createQueryBuilder('e')
            ->join('e.site', 's')
            ->join('e.organiser', 'o')
            ->addSelect('s', 'o')
            ->andWhere('e.state NOT IN (:states)')
            ->setParameter('states', [State::CREATED, State::ARCHIVED])
            ->andWhere('e.private = :isPrivate')
            ->setParameter('isPrivate', false);

        if (!empty($search)) {
            $qb->andWhere('(
            e.title LIKE :search OR
            e.eventDescription LIKE :search OR
            o.username LIKE :search OR
            o.firstname LIKE :search OR
            o.lastname LIKE :search OR
            s.name LIKE :search
        )')
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

        if (!empty($dateFrom)) {
            $qb->andWhere('e.dateTimeStart >= :dateFrom')
                ->setParameter('dateFrom', new \DateTime($dateFrom));
        }

        if (!empty($dateTo)) {
            $qb->andWhere('e.dateTimeStart <= :dateTo')
                ->setParameter('dateTo', new \DateTime($dateTo . ' 23:59:59'));
        }

        if ($includePast) {
            $qb->andWhere('e.dateTimeStart < :today')
                ->setParameter('today', new \DateTime('today'));
        } else {
            $qb->andWhere('e.dateTimeStart >= :today')
                ->setParameter('today', new \DateTime('today'));
        }

        if ($myEvents && $participant) {
            $qb->join('e.participants', 'p')
                ->andWhere('p.id = :participantId')
                ->setParameter('participantId', $participant->getId());
        }

        $countQb = $this->createQueryBuilder('e')
            ->select('COUNT(DISTINCT e.id)')
            ->join('e.site', 's')
            ->join('e.organiser', 'o')
            ->andWhere('e.state NOT IN (:states)')
            ->setParameter('states', [State::CREATED, State::ARCHIVED])
            ->andWhere('e.private = :isPrivate')
            ->setParameter('isPrivate', false);

        if (!empty($search)) {
            $countQb->andWhere('(
            e.title LIKE :search OR
            e.eventDescription LIKE :search OR
            o.username LIKE :search OR
            o.firstname LIKE :search OR
            o.lastname LIKE :search OR
            s.name LIKE :search
        )')
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

        if (!empty($dateFrom)) {
            $countQb->andWhere('e.dateTimeStart >= :dateFrom')
                ->setParameter('dateFrom', new \DateTime($dateFrom));
        }

        if (!empty($dateTo)) {
            $countQb->andWhere('e.dateTimeStart <= :dateTo')
                ->setParameter('dateTo', new \DateTime($dateTo . ' 23:59:59'));
        }

        if ($includePast) {
            $countQb->andWhere('e.dateTimeStart < :today')
                ->setParameter('today', new \DateTime('today'));
        } else {
            $countQb->andWhere('e.dateTimeStart >= :today')
                ->setParameter('today', new \DateTime('today'));
        }

        if ($myEvents && $participant) {
            $countQb->join('e.participants', 'p')
                ->andWhere('p.id = :participantId')
                ->setParameter('participantId', $participant->getId());
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


    public function findEventsByUserParticipated(Participant $participant): array {
        return $this->createQueryBuilder('e')
            ->join('e.participants', 'p')
            ->andWhere('p.id = :id')
            ->setParameter('id', $participant->getId())
            ->addOrderBy('e.dateTimeStart', 'ASC')
            ->getQuery()
            ->getResult();
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

    public function findAvailable()
    {

        return $this->createQueryBuilder('e')
            ->where('e.state NOT IN  (:states)')
            ->setParameter('states', [State::CREATED, State::ARCHIVED])
            ->where('e.private = :isPrivate')
            ->setParameter('isPrivate', false)
            ->getQuery()
            ->getResult();
    }






}
