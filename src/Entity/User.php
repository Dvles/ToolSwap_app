<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 150)]
    private ?string $firstName = null;

    #[ORM\Column(length: 150)]
    private ?string $lastName = null;

    #[ORM\Column(length: 15, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    #[ORM\Column(length: 255)]
    private ?string $community = null;

    #[ORM\Column(nullable: true)]
    private ?int $rewards = null;

    /**
     * @var Collection<int, Tool>
     */
    #[ORM\OneToMany(targetEntity: Tool::class, mappedBy: "owner", fetch: "EAGER", orphanRemoval: true)] // EAGER to instruct Doctrine to load the toolsOwned collection at the same time as the User
    private Collection $toolsOwned;

    /**
     * @var Collection<int, ToolReview>
     */
    #[ORM\OneToMany(targetEntity: ToolReview::class, mappedBy: 'userOfReview', orphanRemoval: true)]
    private Collection $userReviews;

    /**
     * @var Collection<int, BorrowTool>
     */
    #[ORM\OneToMany(targetEntity: BorrowTool::class, mappedBy: 'userBorrower', orphanRemoval: true)]
    private Collection $borrowTools;

    /**
     * @var Collection<int, LenderReview>
     */
    #[ORM\OneToMany(targetEntity: LenderReview::class, mappedBy: 'userLeavingReview', orphanRemoval: true)]
    private Collection $reviewsLeft;

    /**
     * @var Collection<int, LenderReview>
     */
    #[ORM\OneToMany(targetEntity: LenderReview::class, mappedBy: 'userBeingReviewed', orphanRemoval: true)]
    private Collection $reviewsReceived;

    /**
     * @var Collection<int, ToolAvailability>
     */
    #[ORM\OneToMany(targetEntity: ToolAvailability::class, mappedBy: 'user')]
    #[Groups(['tool:read'])]
    private Collection $toolAvailabilities;



    public function __construct()
    {
        $this->roles[] = 'ROLE_USER';
        $this->toolsOwned = new ArrayCollection();
        $this->userReviews = new ArrayCollection();
        $this->borrowTools = new ArrayCollection();
        $this->reviewsLeft = new ArrayCollection();
        $this->reviewsReceived = new ArrayCollection();
        $this->toolAvailabilities = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;
        return $this;
    }

    public function getCommunity(): ?string
    {
        return $this->community;
    }

    public function setCommunity(string $community): static
    {
        $this->community = $community;
        return $this;
    }

    public function getRewards(): ?int
    {
        return $this->rewards;
    }

    public function setRewards(?int $rewards): static
    {
        $this->rewards = $rewards;
        return $this;
    }

    /**
     * @return Collection<int, Tool>
     */
    public function getToolsOwned(): Collection
    {
        return $this->toolsOwned;
    }

    public function addTool(Tool $tool): static
    {
        if (!$this->toolsOwned->contains($tool)) {
            $this->toolsOwned->add($tool);
            $tool->setOwner($this);
        }
        return $this;
    }

    public function removeTool(Tool $tool): static
    {
        if ($this->toolsOwned->removeElement($tool)) {
            if ($tool->getOwner() === $this) {
                $tool->setOwner(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, ToolReview>
     */
    public function getUserReviews(): Collection
    {
        return $this->userReviews;
    }

    public function addUserReview(ToolReview $userReview): static
    {
        if (!$this->userReviews->contains($userReview)) {
            $this->userReviews->add($userReview);
            $userReview->setUserOfReview($this);
        }
        return $this;
    }

    public function removeUserReview(ToolReview $userReview): static
    {
        if ($this->userReviews->removeElement($userReview)) {
            if ($userReview->getUserOfReview() === $this) {
                $userReview->setUserOfReview(null);
            }
        }
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
            $borrowTool->setUserBorrower($this);
        }
        return $this;
    }

    public function removeBorrowTool(BorrowTool $borrowTool): static
    {
        if ($this->borrowTools->removeElement($borrowTool)) {
            if ($borrowTool->getUserBorrower() === $this) {
                $borrowTool->setUserBorrower(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, LenderReview>
     */
    public function getReviewsLeft(): Collection
    {
        return $this->reviewsLeft;
    }

    public function addReviewsLeft(LenderReview $reviewsLeft): static
    {
        if (!$this->reviewsLeft->contains($reviewsLeft)) {
            $this->reviewsLeft->add($reviewsLeft);
            $reviewsLeft->setUserLeavingReview($this); 
        }
        return $this;
    }

    public function removeReviewsLeft(LenderReview $reviewsLeft): static
    {
        if ($this->reviewsLeft->removeElement($reviewsLeft)) {
            if ($reviewsLeft->getUserLeavingReview() === $this) { 
                $reviewsLeft->setUserLeavingReview(null); 
            }
        }
        return $this;
    }


    /**
     * @return Collection<int, LenderReview>
     */
    public function getReviewsReceived(): Collection
    {
        return $this->reviewsReceived;
    }

    public function addReviewsReceived(LenderReview $reviewsReceived): static
    {
        if (!$this->reviewsReceived->contains($reviewsReceived)) {
            $this->reviewsReceived->add($reviewsReceived);
            $reviewsReceived->setUserBeingReviewed($this); 
        }
        return $this;
    }

    public function removeReviewsReceived(LenderReview $reviewsReceived): static
    {
        if ($this->reviewsReceived->removeElement($reviewsReceived)) {
            if ($reviewsReceived->getUserBeingReviewed() === $this) { 
                $reviewsReceived->setUserBeingReviewed(null); 
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
            $toolAvailability->setUser($this);
        }

        return $this;
    }

    public function removeToolAvailability(ToolAvailability $toolAvailability): static
    {
        if ($this->toolAvailabilities->removeElement($toolAvailability)) {
            // set the owning side to null (unless already changed)
            if ($toolAvailability->getUser() === $this) {
                $toolAvailability->setUser(null);
            }
        }

        return $this;
    }



}
