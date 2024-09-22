<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

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
    #[ORM\OneToMany(targetEntity: Tool::class, mappedBy: 'UserOfTool', orphanRemoval: true)]
    private Collection $ToolOfUser;

    /**
     * @var Collection<int, ToolReview>
     */
    #[ORM\OneToMany(targetEntity: ToolReview::class, mappedBy: 'UserOfReview', orphanRemoval: true)]
    private Collection $userReviews;

    /**
     * @var Collection<int, BorrowTool>
     */
    #[ORM\OneToMany(targetEntity: BorrowTool::class, mappedBy: 'userBorrower')]
    private Collection $borrowTool;

    public function __construct()
    {
        // Set default role for all new users
        $this->roles[] = 'ROLE_USER';
        $this->ToolOfUser = new ArrayCollection();
        $this->userReviews = new ArrayCollection();
        $this->borrowTool = new ArrayCollection();
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
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
        // Guarantee every user has at least ROLE_USER
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

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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
    public function getToolOfUser(): Collection
    {
        return $this->ToolOfUser;
    }

    public function addToolOfUser(Tool $toolOfUser): static
    {
        if (!$this->ToolOfUser->contains($toolOfUser)) {
            $this->ToolOfUser->add($toolOfUser);
            $toolOfUser->setUserOfTool($this);
        }

        return $this;
    }

    public function removeToolOfUser(Tool $toolOfUser): static
    {
        if ($this->ToolOfUser->removeElement($toolOfUser)) {
            // set the owning side to null (unless already changed)
            if ($toolOfUser->getUserOfTool() === $this) {
                $toolOfUser->setUserOfTool(null);
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
            // set the owning side to null (unless already changed)
            if ($userReview->getUserOfReview() === $this) {
                $userReview->setUserOfReview(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BorrowTool>
     */
    public function getBorrowTool(): Collection
    {
        return $this->borrowTool;
    }

    public function addBorrowTool(BorrowTool $borrowTool): static
    {
        if (!$this->borrowTool->contains($borrowTool)) {
            $this->borrowTool->add($borrowTool);
            $borrowTool->setUserBorrower($this);
        }

        return $this;
    }

    public function removeBorrowTool(BorrowTool $borrowTool): static
    {
        if ($this->borrowTool->removeElement($borrowTool)) {
            // set the owning side to null (unless already changed)
            if ($borrowTool->getUserBorrower() === $this) {
                $borrowTool->setUserBorrower(null);
            }
        }

        return $this;
    }
}
