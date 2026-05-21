<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Location;
use App\Entity\Participant;
use App\Entity\Site;
use App\Enum\State;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        $participants = $manager
            ->getRepository(Participant::class)
            ->findAll();

        $sites = $manager
            ->getRepository(Site::class)
            ->findAll();

        $locations = $manager
            ->getRepository(Location::class)
            ->findAll();

        for ($i = 0; $i < 10; $i++) {

            $event = new Event();

            // Date de début
            $startDate = \DateTimeImmutable::createFromMutable(
                $faker->dateTimeBetween('+1 day', '+3 months')
            );

            // Date limite inscription
            $limitDate = $startDate->modify(
                '-' . rand(1, 15) . ' days'
            );

            // Durée
            $hours = rand(1, 5);
            $minutes = rand(0, 59);

            $duration = new \DateInterval(
                "PT{$hours}H{$minutes}M"
            );

            // Hydratation
            $event->setTitle(
               "Voyage " . $faker->country()
            );

            $event->setDateTimeStart($startDate);

            $event->setDateLimitRegistration($limitDate);

            $event->setDuration($duration);

            $event->setNbParticipants(
                rand(5, 50)
            );

            $event->setEventDescription(
                $faker->paragraph()
            );

            $event->setState(
                $faker->randomElement(State::cases())
            );

            // Organisateur
            $organiser = $faker->randomElement($participants);

            $event->setOrganiser($organiser);

            // Site
            $event->setSite(
                $faker->randomElement($sites)
            );

            // Location
            $event->setLocation(
                $faker->randomElement($locations)
            );

            // Participants
            $randomParticipants = $faker->randomElements(
                $participants,
                rand(2, 8)
            );

            foreach ($randomParticipants as $participant) {
                $event->addParticipant($participant);
            }

            $manager->persist($event);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            ParticipantFixtures::class,
            SiteFixtures::class,
            LocationFixtures::class,
        ];
    }
}
