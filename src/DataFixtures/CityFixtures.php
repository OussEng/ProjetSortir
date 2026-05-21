<?php

namespace App\DataFixtures;

use App\Entity\City;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class CityFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        for ($i = 0; $i < 15; $i++) {

            $city = new City();

            $city->setName($faker->city());

            // zip code FR en 5 chiffres
            $city->setZipcode(
                $faker->numerify('#####')
            );
            $manager->persist($city);
        }
        $manager->flush();
    }
}
