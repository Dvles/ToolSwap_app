<?php

namespace App\Entity;

use App\Repository\ToolCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ToolCategoryRepository::class)]
class ToolCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    /**
     * @var Collection<int, Tool>
     */
    #[ORM\OneToMany(targetEntity: Tool::class, mappedBy: 'toolCategory')]
    private Collection $toolsInCategory;

    public function __construct()
    {
        $this->toolsInCategory = new ArrayCollection();
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

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Tool>
     */
    public function getToolsInCategory(): Collection
    {
        return $this->toolsInCategory;
    }

    public function addToolsInCategory(Tool $toolsInCategory): static
    {
        if (!$this->toolsInCategory->contains($toolsInCategory)) {
            $this->toolsInCategory->add($toolsInCategory);
            $toolsInCategory->setToolCategory($this);
        }

        return $this;
    }

    public function removeToolsInCategory(Tool $toolsInCategory): static
    {
        if ($this->toolsInCategory->removeElement($toolsInCategory)) {
            // set the owning side to null (unless already changed)
            if ($toolsInCategory->getToolCategory() === $this) {
                $toolsInCategory->setToolCategory(null);
            }
        }

        return $this;
    }
}
