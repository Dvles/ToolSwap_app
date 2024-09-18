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
}
