<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $sites = $manager->getRepository(Site::class)->findAll();

        if (count($sites) === 0) {
            throw new \Exception("Aucun Site trouvé. Lance SiteFixtures avant ParticipantFixtures.");
        }

        for ($i = 0; $i < 500; $i++) {

            $participant = new Participant();

            $participant->setEmail($faker->unique()->safeEmail());
            $participant->setRoles(['ROLE_USER']);
            $participant->setUsername($faker->userName());
            $participant->setFirstname($faker->firstName());
            $participant->setLastname($faker->lastName());
            $participant->setActive($faker->boolean(90));
            $participant->setPhone($faker->phoneNumber());
            $participant->setImg(null);

            $participant->setPassword(
                $this->passwordHasher->hashPassword($participant, 'password')
            );

            $participant->setSite(
                $faker->randomElement($sites)
            );

            $manager->persist($participant);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            SiteFixtures::class,
        ];
    }
}
