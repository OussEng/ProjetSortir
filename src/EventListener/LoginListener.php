<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

class LoginListener
{
    #[AsEventListener]
    public function __invoke(LoginSuccessEvent $event): void
    {
        $event->getRequest()->getSession()->getFlashBag()->add('success', 'Bienvenue !');
    }

}
