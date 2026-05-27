<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Location;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LocationFixtures extends Fixture
{
        public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Récupération des villes existantes
        $cities = $manager
            ->getRepository(City::class)
            ->findAll();

        for ($i = 0; $i < 100; $i++) {

            $location = new Location();

            $location->setName(
                $faker->company()
            );

            $location->setAddress(
                $faker->streetAddress()
            );

            // Ville aléatoire
            $location->setCity(
                $faker->randomElement($cities)
            );

            $manager->persist($location);
        }

        $manager->flush();
    }

}
