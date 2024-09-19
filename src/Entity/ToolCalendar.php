<?php

namespace App\Entity;

use App\Repository\ToolCalendarRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ToolCalendarRepository::class)]
class ToolCalendar
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $availableDates = [];

    #[ORM\OneToOne(mappedBy: 'ToolCalendar', cascade: ['persist', 'remove'])]
    private ?Tool $toolOfCalendar = null;

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

    public function getToolOfCalendar(): ?Tool
    {
        return $this->toolOfCalendar;
    }

    public function setToolOfCalendar(?Tool $toolOfCalendar): static
    {
        // unset the owning side of the relation if necessary
        if ($toolOfCalendar === null && $this->toolOfCalendar !== null) {
            $this->toolOfCalendar->setToolCalendar(null);
        }

        // set the owning side of the relation if necessary
        if ($toolOfCalendar !== null && $toolOfCalendar->getToolCalendar() !== $this) {
            $toolOfCalendar->setToolCalendar($this);
        }

        $this->toolOfCalendar = $toolOfCalendar;

        return $this;
    }
}
