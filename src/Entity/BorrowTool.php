<?php

namespace App\Entity;

use App\Enum\ToolStatusEnum;
use App\Repository\BorrowToolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BorrowToolRepository::class)]
class BorrowTool
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(enumType: ToolStatusEnum::class)]
    private ?ToolStatusEnum $status = null;

    #[ORM\ManyToOne(inversedBy: 'borrowTool')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userBorrower = null;

    #[ORM\ManyToOne(inversedBy: 'toolBorrowed')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tool $toolBeingBorrowed = null;

    
    

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getStatus(): ?ToolStatusEnum
    {
        return $this->status;
    }

    public function setStatus(ToolStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getUserBorrower(): ?User
    {
        return $this->userBorrower;
    }

    public function setUserBorrower(?User $userBorrower): static
    {
        $this->userBorrower = $userBorrower;

        return $this;
    }

    public function getToolBeingBorrowed(): ?Tool
    {
        return $this->toolBeingBorrowed;
    }

    public function setToolBeingBorrowed(?Tool $toolBeingBorrowed): static
    {
        $this->toolBeingBorrowed = $toolBeingBorrowed;

        return $this;
    }

    
}
