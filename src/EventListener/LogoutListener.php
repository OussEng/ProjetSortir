<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LogoutEvent;

#[AsEventListener]
class LogoutListener
{
    public function __invoke(LogoutEvent $event): void
    {
        $event->getRequest()->getSession()->getFlashBag()->add('success', 'Vous avez été déconnecté.');
    }


}
