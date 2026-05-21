<?php

namespace App\Service;

use App\Repository\SiteRepository;

class SiteService {

    public function __construct(private SiteRepository $siteRepository){
    }

    public function getAllSites(){
        return $this->siteRepository->findAll();
    }

    public function getSite(int $id){
        return $this->siteRepository->find($id);
    }

    public function getByName(string $name){
        return $this->siteRepository->findOneBy((['name' => $name]));
    }



}
