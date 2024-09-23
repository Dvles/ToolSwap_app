<?php

namespace App\Entity;

use App\Repository\LenderReviewRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LenderReviewRepository::class)]
class LenderReview
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $rating = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comments = null;

    #[ORM\ManyToOne(inversedBy: 'reviewsLeft')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userLeavingReview = null;

    #[ORM\ManyToOne(inversedBy: 'reviewsReceived')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userBeingReviewed = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(int $rating): static
    {
        $this->rating = $rating;
        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): static
    {
        $this->comments = $comments;
        return $this;
    }

    public function getUserLeavingReview(): ?User
    {
        return $this->userLeavingReview;
    }

    public function setUserLeavingReview(?User $userLeavingReview): static
    {
        $this->userLeavingReview = $userLeavingReview;
        return $this;
    }

    public function getUserBeingReviewed(): ?User
    {
        return $this->userBeingReviewed;
    }

    public function setUserBeingReviewed(?User $userBeingReviewed): static
    {
        $this->userBeingReviewed = $userBeingReviewed;
        return $this;
    }
}
