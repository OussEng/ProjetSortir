<?php

namespace App\Entity;

use App\Enum\State;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateTimeStart = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $dateLimitRegistration = null;

    #[ORM\Column(nullable: true)]
    private ?\DateInterval $duration = null;

    #[ORM\Column]
    private ?int $nbParticipants = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $eventDescription = null;

    #[ORM\Column(type: 'string', enumType: State::class)]
    private State $state = State::CREATED;

    #[ORM\ManyToOne(inversedBy: 'organisedEvents')]
    private ?Participant $organiser = null;

    /**
     * @var Collection<int, Participant>
     */
    #[ORM\ManyToMany(targetEntity: Participant::class, inversedBy: 'events')]
    private Collection $participants;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Site $site = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Location $location = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDateTimeStart(): ?\DateTimeImmutable
    {
        return $this->dateTimeStart;
    }

    public function setDateTimeStart(\DateTimeImmutable $dateTimeStart): static
    {
        $this->dateTimeStart = $dateTimeStart;

        return $this;
    }

    public function getDateLimitRegistration(): ?\DateTimeImmutable
    {
        return $this->dateLimitRegistration;
    }

    public function setDateLimitRegistration(\DateTimeImmutable $dateLimitRegistration): static
    {
        $this->dateLimitRegistration = $dateLimitRegistration;

        return $this;
    }

    public function getDuration(): ?\DateInterval
    {
        return $this->duration;
    }

    public function setDuration(?\DateInterval $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getNbParticipants(): ?int
    {
        return $this->nbParticipants;
    }

    public function setNbParticipants(int $nbParticipants): static
    {
        $this->nbParticipants = $nbParticipants;

        return $this;
    }

    public function getEventDescription(): ?string
    {
        return $this->eventDescription;
    }

    public function setEventDescription(string $eventDescription): static
    {
        $this->eventDescription = $eventDescription;

        return $this;
    }


    public function getState(): State
    {
        return $this->state;
    }

    public function setState(State $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getOrganiser(): ?participant
    {
        return $this->organiser;
    }

    public function setOrganiser(?participant $organiser): static
    {
        $this->organiser = $organiser;

        return $this;
    }

    /**
     * @return Collection<int, participant>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(participant $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
        }

        return $this;
    }

    public function removeParticipant(participant $participant): static
    {
        $this->participants->removeElement($participant);

        return $this;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): static
    {
        $this->site = $site;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function isOpen() : bool
    {
        return $this->state === State::OPEN;
    }


    public function isCancelled() : bool
    {
        return $this->state === State::CANCELED;
    }

    public function isCreated() : bool
    {
        return $this->state === State::CREATED;
    }

}
