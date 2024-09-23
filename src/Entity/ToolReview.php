<?php

namespace App\Entity;

use App\Repository\ToolReviewRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ToolReviewRepository::class)]
class ToolReview
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $rating = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $comment = null;

    #[ORM\ManyToOne(inversedBy: 'userReviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userOfReview = null;

    #[ORM\ManyToOne(inversedBy: 'toolReviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tool $toolOfReview = null;

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

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;
        return $this;
    }

    public function getUserOfReview(): ?User
    {
        return $this->userOfReview;
    }

    public function setUserOfReview(?User $userOfReview): static
    {
        $this->userOfReview = $userOfReview;
        return $this;
    }

    public function getToolOfReview(): ?Tool
    {
        return $this->toolOfReview;
    }

    public function setToolOfReview(?Tool $toolOfReview): static
    {
        $this->toolOfReview = $toolOfReview;
        return $this;
    }
}
