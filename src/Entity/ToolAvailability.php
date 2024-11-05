<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ToolAvailabilityRepository;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ToolAvailabilityRepository::class)]
class ToolAvailability
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["tool:read"])]
    private ?int $id = null;


    // #[ORM\Column(length: 255, nullable: true)]
    // #[Groups(["tool:read"])]
    // private ?string $title = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(["tool:read"])]
    private ?\DateTimeInterface $start = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(["tool:read"])]
    private ?\DateTimeInterface $end = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["tool:read"])]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Groups(["tool:read"])]
    private ?string $background_color = null;

    #[ORM\Column(length: 255)]
    #[Groups(["tool:read"])]
    private ?string $border_color = null;

    #[ORM\Column(length: 255)]
    private ?string $text_color = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isAvailable = true; // Default to true (available)

    #[ORM\ManyToOne(inversedBy: 'toolAvailabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'toolAvailabilities')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tool $tool = null;

    #[ORM\ManyToOne(inversedBy: 'toolAvailabilities')]
    #[ORM\JoinColumn(nullable: true)] // Can be nullable because not every ToolAvailability will be borrowed
    private ?BorrowTool $borrowTool = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    // public function getTitle(): ?string
    // {
    //     return $this->title;
    // }

    // public function setTitle(?string $title): static
    // {
    //     $this->title = $title;

    //     return $this;
    // }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(\DateTimeInterface $start): static
    {
        $this->start = $start;

        return $this;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(\DateTimeInterface $end): static
    {
        $this->end = $end;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getBackgroundColor(): ?string
    {
        return $this->background_color;
    }

    public function setBackgroundColor(string $background_color): static
    {
        $this->background_color = $background_color;

        return $this;
    }

    public function getBorderColor(): ?string
    {
        return $this->border_color;
    }

    public function setBorderColor(string $border_color): static
    {
        $this->border_color = $border_color;

        return $this;
    }

    public function getTextColor(): ?string
    {
        return $this->text_color;
    }

    public function setTextColor(string $text_color): static
    {
        $this->text_color = $text_color;

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


    public function getTool(): ?Tool
    {
        return $this->tool;
    }

    public function setTool(?Tool $tool): static
    {
        $this->tool = $tool;

        return $this;
    }

    public function isAvailable(): bool
    {
        return $this->isAvailable;
    }

    public function setIsAvailable(bool $isAvailable): static
    {
        $this->isAvailable = $isAvailable;

        return $this;
    }

    public function getBorrowTool(): ?BorrowTool
    {
        return $this->borrowTool;
    }

    public function setBorrowTool(?BorrowTool $borrowTool): self
    {
        $this->borrowTool = $borrowTool;

        return $this;
    }
}
