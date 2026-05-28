<?php

namespace App\Service;

use App\Repository\PrivateGroupRepository;

class PrivateGroupService
{


    public function __construct(private PrivateGroupRepository $privateGroupRepository)
    {
    }


    public function getById(int $id)
    {
        return $this->privateGroupRepository->find($id);
    }
}
