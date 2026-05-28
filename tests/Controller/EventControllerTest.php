<?php

namespace App\Tests\Controller;

use App\Service\LocationService;
use App\Service\ParticipantService;
use App\Service\SiteService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class EventControllerTest extends WebTestCase
{
    public function testCreateEvent(): void
    {
        $client = static::createClient();
        $now = new \DateTime();
        $dateTimeStart = (clone $now)->modify('+7 days')->format('Y-m-d\TH:i');
        $dateLimitRegistration = (clone $now)->modify('+5 days')->format('Y-m-d\TH:i');
        $userService = static::getContainer()->get(ParticipantService::class);
        $siteService = static::getContainer()->get(SiteService::class);
        $locationService = static::getContainer()->get(LocationService::class);

        $site = $siteService->getOneSite(3);
        $location = $locationService->getOneLocation(3);
        $testUser = $userService->getOneParticipant(2);

        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/sortie/cree');
        $this->assertResponseIsSuccessful( "Création d'une sortie");

        $form = $crawler->selectButton('Créer la sortie')->form([
            'update_event[title]'                       => 'Sortie test automatisée',
            'update_event[dateTimeStart]'               => $dateTimeStart,
            'update_event[dateLimitRegistration]'       => $dateLimitRegistration,
            'update_event[duration][days]'              => '0',
            'update_event[duration][hours]'             => '2',
            'update_event[duration][minutes]'           => '30',
            'update_event[nbParticipants]'              => '20',
            'update_event[eventDescription]'            => 'Description de la sortie de test.',
            'update_event[location]'                    => $location->getId(),
        ]);
        $client->submit($form);
        $this->assertResponseRedirects('/sortie');

    }
}
