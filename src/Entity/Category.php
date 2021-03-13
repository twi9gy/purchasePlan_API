<?php

namespace App\Entity;

use App\Model\Request\CategoryDtoRequest;
use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CategoryRepository::class)
 */
class Category
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="categories")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user_id;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class)
     * @ORM\JoinColumn(nullable=true)
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity=SalesFile::class, mappedBy="category", cascade={"remove"})
     */
    private $salesFiles;

    public function __construct()
    {
        $this->salesFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUserId(): ?User
    {
        return $this->user_id;
    }

    public function setUserId(?User $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public static function fromDto(CategoryDtoRequest $categoryDto): self
    {
        $category = new self();
        $category->setParent($categoryDto->parent_id);
        $category->setName($categoryDto->name);
        return $category;
    }

    /**
     * @return Collection|SalesFile[]
     */
    public function getSalesFiles(): Collection
    {
        return $this->salesFiles;
    }

    public function addSalesFile(SalesFile $salesFile): self
    {
        if (!$this->salesFiles->contains($salesFile)) {
            $this->salesFiles[] = $salesFile;
            $salesFile->setCategory($this);
        }

        return $this;
    }

    public function removeSalesFile(SalesFile $salesFile): self
    {
        // set the owning side to null (unless already changed)
        if ($this->salesFiles->removeElement($salesFile) && $salesFile->getCategory() === $this) {
            $salesFile->setCategory(null);
        }

        return $this;
    }
}
