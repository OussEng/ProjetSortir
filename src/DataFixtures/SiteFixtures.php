<?php

namespace App\DataFixtures;

use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class SiteFixtures extends Fixture
{

    public function load(ObjectManager $manager): void
    {
        $site = new Site();
        $site->setName("Chartres De Bretagne ");
        $manager->persist($site);
        $this->addReference('site_chartres', $site);

        $site1 = new Site();
        $site1->setName("Niort ");
        $this->addReference('site_niort', $site1);
        $manager->persist($site1);

        $site2 = new Site();
        $site2->setName("Nantes ");
        $this->addReference('site_nantes', $site2);
        $manager->persist($site2);

        $site3 = new Site();
        $site3->setName("Rennes ");
        $this->addReference('site_rennes', $site3);
        $manager->persist($site3);

        $manager->flush();
    }
}
