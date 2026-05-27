<?php

namespace App\DataFixtures;


use App\Entity\Participant;
use App\Entity\Site;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    public function __construct(
        private UserPasswordHasherInterface $hasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        $user = new Participant();
        $user->setEmail('user@sortir.com');
        $user->setUsername('user123');
        $user->setFirstname('user');
        $user->setLastname('user');
        $user->setPhone('0600000000');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setActive(true);
        $user->setSite($this->getReference('site_nantes', Site::class));
        $user->setPassword($this->hasher->hashPassword($user, 'mdp123'));
        $user->setImg(null);

        $manager->persist($user);
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [SiteFixtures::class];
    }
}
