<?php

namespace App\Entity;

use App\Repository\PrivateGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PrivateGroupRepository::class)]
class PrivateGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\ManyToOne(inversedBy: 'privateGroups')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Participant $organiser = null;

    /**
     * @var Collection<int, Participant>
     */
    #[ORM\ManyToMany(targetEntity: Participant::class, inversedBy: 'privateGroups')]
    private Collection $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getOrganiser(): ?Participant
    {
        return $this->organiser;
    }

    public function setOrganiser(?Participant $organiser): static
    {
        $this->organiser = $organiser;

        return $this;
    }

    /**
     * @return Collection<int, Participant>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(Participant $member): static
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
        }

        return $this;
    }

    public function removeMember(Participant $member): static
    {
        $this->members->removeElement($member);

        return $this;
    }
}
