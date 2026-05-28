<?php

namespace App\Tests\Service;

use App\Entity\Event;
use App\Enum\State;
use App\Repository\EventRepository;
use App\Service\EventService;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\Security;

class EventServiceTest extends TestCase
{
    private EventRepository $eventRepository;
    private Security $security;
    private EventService $eventService;



    protected function setUp(): void
    {
        $this->eventRepository = $this->createMock(EventRepository::class);
        $this->security = $this->createStub(Security::class);
        $this->eventService = new EventService($this->eventRepository, $this->security);
    }

    function testCancelEventChangesStatus(): void
    {
        $event = new Event();
        $event->setState(State::OPEN);

        $reason = "Annulé car ils m'ont saoulé";

        $this->eventRepository
            ->expects($this->once())
            ->method('save')
            ->with($event);

        $this->eventService->cancelEvent($event, $reason);

        $this->assertSame(State::CANCELED, $event->getState());
        $this->assertSame($reason, $event->getEventDescription());
    }

}
