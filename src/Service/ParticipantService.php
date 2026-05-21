<?php

namespace App\Service;

use App\Repository\ParticipantRepository;
use App\Repository\SiteRepository;

readonly class ParticipantService {

    public function __construct(private readonly ParticipantRepository $participantRepository){
    }

    public function getOneParticipantByUsername(string $username)
    {
        return $this->participantRepository->findOneBy(['username' => $username]);
    }
}
