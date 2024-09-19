<?php

namespace App\Entity;

use App\Repository\ToolReviewRepository;
use Doctrine\DBAL\Types\Types;
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

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\ManyToOne(inversedBy: 'userReviews')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $UserOfReview = null;

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
        return $this->UserOfReview;
    }

    public function setUserOfReview(?User $UserOfReview): static
    {
        $this->UserOfReview = $UserOfReview;

        return $this;
    }
}
