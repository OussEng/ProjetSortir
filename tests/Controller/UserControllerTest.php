<?php

namespace App\Tests\Controller;

use App\Service\ParticipantService;
use App\Service\SiteService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UserControllerTest extends WebTestCase
{

    public function testAccessDenied(): void {
        $client = static::createClient();
        $userService = static::getContainer()->get(ParticipantService::class);

        $testUser = $userService->getOneParticipant(2);
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/register');
        $this->assertResponseStatusCodeSame(403, 'Il faut être admin pour accéder à /register !');
    }

    public function testCreateUser(): void
    {
        $client = static::createClient();
        $userService = static::getContainer()->get(ParticipantService::class);
        $siteService = static::getContainer()->get(SiteService::class);
        $site = $siteService->getOneSite(3);

        $testUser = $userService->getOneParticipant(1);
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful("Accès à l'administration");

        $crawler = $client->request('GET', '/register');
        $this->assertResponseIsSuccessful("Accès à la création d'un utilisateur");

        $form = $crawler->selectButton('Inscrire')->form();

        $form['registration_form[firstName]'] = 'Johnf';
        $form['registration_form[lastName]'] = 'Doesf';
        $form['registration_form[userName]'] = 'jdoef';
        $form['registration_form[email]'] = 'johnf@test.com';
        $form['registration_form[phone]'] = '0600000000';
        $form['registration_form[agreeTerms]']->tick();
        $form['registration_form[site]']->select($site->getId());
        $client->submit($form, [
            'registration_form[plainPassword][first]'  => 'password123',
            'registration_form[plainPassword][second]' => 'password123',
        ]);

        $this->assertResponseStatusCodeSame(302);
        $this->assertResponseRedirects();

    }

    public function testDeleteUser(): void {
        $client = static::createClient();
        $userService = static::getContainer()->get(ParticipantService::class);

        $testUser = $userService->getOneParticipant(1);
        $client->loginUser($testUser);
        $crawler = $client->request('GET', '/admin');
        $this->assertResponseIsSuccessful("Accès à l'administration");

        $crawler = $client->request('GET', '/admin/utilisateurs');
        $this->assertResponseIsSuccessful("Accès à la gestion des utilisateurs");

        $userIdToBeDeleted = $userService->getOneParticipantByUsername('jdoef')->getId();
        $crawler = $client->request('POST', '/admin/utilisateurs/supprimer/' . $userIdToBeDeleted);
        $this->assertResponseStatusCodeSame(302, "Utilisateur supprimer");
        $this->assertResponseRedirects('/admin/utilisateurs');
    }
}
