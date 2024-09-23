<?php

namespace App\Entity;

use App\Repository\ToolAvailabilityRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ToolAvailabilityRepository::class)]
class ToolAvailability
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $availableDates = [];

    #[ORM\OneToOne(mappedBy: 'ToolAvailability', cascade: ['persist', 'remove'])]
    private ?Tool $toolOfAvailability = null;

    #[ORM\Column(nullable: true)]
    private ?bool $isRecurring = null;

    #[ORM\Column(type: Types::ARRAY, nullable: true)]
    private ?array $specificDates = null;

    #[ORM\ManyToOne(inversedBy: 'toolAvailabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tool $tool = null;

    #[ORM\ManyToOne(inversedBy: 'toolAvailabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAvailableDates(): array
    {
        return $this->availableDates;
    }

    public function setAvailableDates(array $availableDates): static
    {
        $this->availableDates = $availableDates;

        return $this;
    }

    public function getToolOfAvailability(): ?Tool
    {
        return $this->toolOfAvailability;
    }

    public function setToolOfAvailability(?Tool $toolOfAvailability): static
    {
        // unset the owning side of the relation if necessary
        if ($toolOfAvailability === null && $this->toolOfAvailability !== null) {
            $this->toolOfAvailability->setToolAvailability(null);
        }

        // set the owning side of the relation if necessary
        if ($toolOfAvailability !== null && $toolOfAvailability->getToolAvailability() !== $this) {
            $toolOfAvailability->setToolAvailability($this);
        }

        $this->toolOfAvailability = $toolOfAvailability;

        return $this;
    }

    public function isRecurring(): ?bool
    {
        return $this->isRecurring;
    }

    public function setRecurring(?bool $isRecurring): static
    {
        $this->isRecurring = $isRecurring;

        return $this;
    }

    public function getSpecificDates(): ?array
    {
        return $this->specificDates;
    }

    public function setSpecificDates(?array $specificDates): static
    {
        $this->specificDates = $specificDates;

        return $this;
    }

    public function getTool(): ?Tool
    {
        return $this->tool;
    }

    public function setTool(?Tool $tool): static
    {
        $this->tool = $tool;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
