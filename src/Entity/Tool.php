<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ToolRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ToolRepository::class)]
class Tool
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["tool:read", "tool:write"])]
    private ?int $id = null;

    #[Groups(["tool:read", "tool:write"])]
    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["tool:read"])]
    private ?string $description = null;

    #[ORM\Column(length: 20)]
    #[Groups(["tool:read"])]
    private ?string $toolCondition = null;

    #[Groups(["tool:read", "tool:write"])]
    #[ORM\Column(nullable: true)]
    private ?bool $availability = null;

    #[ORM\Column(type: 'boolean')]
    private bool $isDisabled = false; // Default to false (tool enabled in listing)

    
    
    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2, nullable: true)]
    private ?string $priceDay = null; 
    
    #[ORM\Column(length: 255)]
    private ?string $imageTool = null;
    
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'toolsOwned')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;
    
    /**
     * @var Collection<int, BorrowTool>
     */
    #[ORM\OneToMany(targetEntity: BorrowTool::class, mappedBy: 'toolBeingBorrowed')]
    private Collection $borrowTools;
    
    
    #[ORM\ManyToOne(inversedBy: 'toolsInCategory')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ToolCategory $toolCategory = null;
    
    /**
     * @var Collection<int, ToolReview>
     */
    #[ORM\OneToMany(targetEntity: ToolReview::class, mappedBy: 'toolOfReview', orphanRemoval: true)]
    private Collection $toolReviews;
    
    /**
     * @var Collection<int, ToolAvailability>
     */
    #[ORM\OneToMany(targetEntity: ToolAvailability::class, mappedBy: 'tool', cascade: ['persist', 'remove'])]
    private Collection $toolAvailabilities;
    // Added cascade to automatically persist or remove ToolAvailability entities when a Tool is saved or deleted
    
    // This property is not persisted in the database
    private ?string $keyword = null;
    
    
    
    
    
    public function __construct()
    {
        $this->borrowTools = new ArrayCollection();
        $this->toolReviews = new ArrayCollection();
        $this->toolAvailabilities = new ArrayCollection();
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
    
    public function getOwner(): ?User
    {
        return $this->owner;
    }
    
    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;
        
        return $this;
    }
    
    /**
     * @return Collection<int, BorrowTool>
     */
    public function getBorrowTools(): Collection
    {
        return $this->borrowTools;
    }
    
    public function addBorrowTool(BorrowTool $borrowTool): static
    {
        if (!$this->borrowTools->contains($borrowTool)) {
            $this->borrowTools->add($borrowTool);
            $borrowTool->setToolBeingBorrowed($this);
        }
        
        return $this;
    }
    
    public function removeBorrowTool(BorrowTool $borrowTool): static
    {
        if ($this->borrowTools->removeElement($borrowTool)) {
            if ($borrowTool->getToolBeingBorrowed() === $this) {
                $borrowTool->setToolBeingBorrowed(null);
            }
        }
        
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
    public function getToolReviews(): Collection
    {
        return $this->toolReviews;
    }
    
    public function addToolReview(ToolReview $toolReview): static
    {
        if (!$this->toolReviews->contains($toolReview)) {
            $this->toolReviews->add($toolReview);
            $toolReview->setToolOfReview($this);
        }
        
        return $this;
    }
    
    public function removeToolReview(ToolReview $toolReview): static
    {
        if ($this->toolReviews->removeElement($toolReview)) {
            if ($toolReview->getToolOfReview() === $this) {
                $toolReview->setToolOfReview(null);
            }
        }
        
        return $this;
    }
    
    /**
     * @return Collection<int, ToolAvailability>
     */
    public function getToolAvailabilities(): Collection
    {
        return $this->toolAvailabilities;
    }
    
    public function addToolAvailability(ToolAvailability $toolAvailability): static
    {
        if (!$this->toolAvailabilities->contains($toolAvailability)) {
            $this->toolAvailabilities->add($toolAvailability);
            $toolAvailability->setTool($this);
        }
        
        return $this;
    }
    
    public function removeToolAvailability(ToolAvailability $toolAvailability): static
    {
        if ($this->toolAvailabilities->removeElement($toolAvailability)) {
            // set the owning side to null (unless already changed)
            if ($toolAvailability->getTool() === $this) {
                $toolAvailability->setTool(null);
            }
        }
        
        return $this;
    }
    
    // Getter for keyword
    public function getKeyword(): ?string
    {
        return $this->keyword;
    }
    
    // Setter for keyword
    public function setKeyword(?string $keyword): self
    {
        $this->keyword = $keyword;
        return $this;
    }
    
    public function isDisabled(): bool
    {
        return $this->isDisabled;
    }
    
    public function setIsDisabled(bool $isDisabled): static
    {
        $this->isDisabled = $isDisabled;
    
        return $this;
    }
}