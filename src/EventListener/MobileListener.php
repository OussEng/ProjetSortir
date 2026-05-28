<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

class MobileListener
{

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();
        $userAgent = $request->headers->get('User-Agent');
        $currentRoute = $request->attributes->get('_route');


        if ($this->isMobile($userAgent) && in_array($currentRoute,
                ['app_create', 'app_groupe_create'] ,
                true))
        {
            $response = new Response("Accès interdit depuis un smartphone.", Response::HTTP_FORBIDDEN);
            $event->setResponse($response);
        }


        if ($this->isMobile($userAgent)) {
            $request->getSession()->set('is_mobile', true);
        } else {
            $request->getSession()->set('is_mobile', false);
        }
    }


    private function isMobile(?string $userAgent): bool
    {
        if (!$userAgent) {
            return false;
        }

        return preg_match('/(android|iphone|ipad|mobile|windows phone|blackberry)/i', $userAgent);
    }
}

