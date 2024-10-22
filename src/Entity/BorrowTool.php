<?php

namespace App\Entity;

use App\Enum\ToolStatusEnum;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\BorrowToolRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

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

    #[ORM\OneToMany(mappedBy: 'borrowTool', targetEntity: ToolAvailability::class, cascade: ['persist', 'remove'])]
    private Collection $toolAvailabilities;

    //#[ORM\ManyToOne(inversedBy: 'borrowTools')]
   //#[ORM\JoinColumn(nullable: false)]
    //private ?ToolAvailability $toolAvailability = null; // Needed to display toolAvailabilities when creating BorrowTool object




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

    public function __construct()
    {
        $this->toolAvailabilities = new ArrayCollection();
    }
    
    public function getToolAvailabilities(): Collection
    {
        return $this->toolAvailabilities;
    }
    
    public function addToolAvailability(ToolAvailability $toolAvailability): self
    {
        if (!$this->toolAvailabilities->contains($toolAvailability)) {
            $this->toolAvailabilities[] = $toolAvailability;
            $toolAvailability->setBorrowTool($this); // Ensure the reverse association is set
        }
    
        return $this;
    }
    
    public function removeToolAvailability(ToolAvailability $toolAvailability): self
    {
        if ($this->toolAvailabilities->removeElement($toolAvailability)) {
            // set the owning side to null (unless already changed)
            if ($toolAvailability->getBorrowTool() === $this) {
                $toolAvailability->setBorrowTool(null);
            }
        }
    
        return $this;
    }

    public function setToolAvailability(?ToolAvailability $toolAvailability): static
    {
        $this->toolAvailability = $toolAvailability;

        return $this;
    }
}
