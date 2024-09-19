<?php

namespace App\Entity;

use App\Repository\ToolRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ToolRepository::class)]
class Tool
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    private ?string $toolCondition = null;

    #[ORM\Column(nullable: true)]
    private ?bool $availability = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $priceDay = null;

    #[ORM\Column(length: 255)]
    private ?string $imageTool = null;

    #[ORM\ManyToOne(inversedBy: 'ToolOfUser')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $UserOfTool = null;

    /**
     * @var Collection<int, BorrowTool>
     */
    #[ORM\OneToMany(targetEntity: BorrowTool::class, mappedBy: 'toolBeingBorrowed')]
    private Collection $toolBorrowed;

    #[ORM\OneToOne(inversedBy: 'toolOfCalendar', cascade: ['persist', 'remove'])]
    private ?ToolCalendar $ToolCalendar = null;

    #[ORM\ManyToOne(inversedBy: 'toolsInCategory')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ToolCategory $toolCategory = null;

    /**
     * @var Collection<int, ToolReview>
     */
    #[ORM\OneToMany(targetEntity: ToolReview::class, mappedBy: 'toolOfReview', orphanRemoval: true)]
    private Collection $toolReview;

    public function __construct()
    {
        $this->toolBorrowed = new ArrayCollection();
        $this->toolReview = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getToolCondition(): ?string
    {
        return $this->toolCondition;
    }

    public function setToolCondition(string $toolCondition): static
    {
        $this->toolCondition = $toolCondition;

        return $this;
    }

    public function isAvailability(): ?bool
    {
        return $this->availability;
    }

    public function setAvailability(?bool $availability): static
    {
        $this->availability = $availability;

        return $this;
    }

    public function getPriceDay(): ?string
    {
        return $this->priceDay;
    }

    public function setPriceDay(?string $priceDay): static
    {
        $this->priceDay = $priceDay;

        return $this;
    }

    public function getImageTool(): ?string
    {
        return $this->imageTool;
    }

    public function setImageTool(string $imageTool): static
    {
        $this->imageTool = $imageTool;

        return $this;
    }

    public function getUserOfTool(): ?User
    {
        return $this->UserOfTool;
    }

    public function setUserOfTool(?User $UserOfTool): static
    {
        $this->UserOfTool = $UserOfTool;

        return $this;
    }

    /**
     * @return Collection<int, BorrowTool>
     */
    public function getToolBorrowed(): Collection
    {
        return $this->toolBorrowed;
    }

    public function addToolBorrowed(BorrowTool $toolBorrowed): static
    {
        if (!$this->toolBorrowed->contains($toolBorrowed)) {
            $this->toolBorrowed->add($toolBorrowed);
            $toolBorrowed->setToolBeingBorrowed($this);
        }

        return $this;
    }

    public function removeToolBorrowed(BorrowTool $toolBorrowed): static
    {
        if ($this->toolBorrowed->removeElement($toolBorrowed)) {
            // set the owning side to null (unless already changed)
            if ($toolBorrowed->getToolBeingBorrowed() === $this) {
                $toolBorrowed->setToolBeingBorrowed(null);
            }
        }

        return $this;
    }

    public function getToolCalendar(): ?ToolCalendar
    {
        return $this->ToolCalendar;
    }

    public function setToolCalendar(?ToolCalendar $ToolCalendar): static
    {
        $this->ToolCalendar = $ToolCalendar;

        return $this;
    }

    public function getToolCategory(): ?ToolCategory
    {
        return $this->toolCategory;
    }

    public function setToolCategory(?ToolCategory $toolCategory): static
    {
        $this->toolCategory = $toolCategory;

        return $this;
    }

    /**
     * @return Collection<int, ToolReview>
     */
    public function getToolReview(): Collection
    {
        return $this->toolReview;
    }

    public function addToolReview(ToolReview $toolReview): static
    {
        if (!$this->toolReview->contains($toolReview)) {
            $this->toolReview->add($toolReview);
            $toolReview->setToolOfReview($this);
        }

        return $this;
    }

    public function removeToolReview(ToolReview $toolReview): static
    {
        if ($this->toolReview->removeElement($toolReview)) {
            // set the owning side to null (unless already changed)
            if ($toolReview->getToolOfReview() === $this) {
                $toolReview->setToolOfReview(null);
            }
        }

        return $this;
    }
}
