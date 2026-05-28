<?php

namespace App\Service;

use App\Entity\Participant;
use App\Repository\ParticipantRepository;
use Doctrine\ORM\EntityManagerInterface;

readonly class ParticipantService {


    public function __construct(private readonly ParticipantRepository  $participantRepository,
                                private readonly EntityManagerInterface $entityManager) {
    }

    /**
     * @param string $username
     * @return Participant|null
     */
    public function getOneParticipantByUsername(string $username): Participant|null
    {
        return $this->participantRepository->findOneBy(['username' => $username]);
    }

    /**
     * @param int $id
     * @return Participant|null
     */
    public function getOneParticipant(int $id): Participant|null
    {
        return $this->participantRepository->find($id);
    }

    /**
     * @return Participant[]|array|object[]
     */
    public function getParticipants(): array
    {
        return $this->participantRepository->findAll();
    }

    public function deleteUser(int $id): void{
        $user = $this->participantRepository->find($id);

        if ($user) {
            $this->entityManager->remove($user);
            $this->entityManager->flush();
        }
    }

    public function desactivateUser(int $id): void{
        $user = $this->participantRepository->find($id);

        if ($user) {
            $user->setActive(0);

            $this->entityManager->flush();
        }
    }

    public function activedUser(int $id): void{
        $user = $this->participantRepository->find($id);

        if ($user) {
            $user->setActive(1);

            $this->entityManager->flush();
        }
    }


    public function getPaginatedParticipants(int $page, int $limit): array
    {
        $offset = ($page - 1) * $limit;

        $totalUsers = $this->participantRepository->count([]);

        $totalPages = ceil($totalUsers / $limit);

        $results = $this->participantRepository->findBy([], ['id' => 'ASC'], $limit, $offset);

        return [
            'results'    => $results,
            'totalPages' => (int) $totalPages
        ];
    }
}
