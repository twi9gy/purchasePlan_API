<?php

namespace App\Entity;

use App\Model\CategoryDto;
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
    private $purchase_user;

    /**
     * @ORM\OneToMany(targetEntity=SalesFile::class, mappedBy="category", cascade={"remove"})
     */
    private $salesFiles;

    /**
     * @ORM\OneToMany(targetEntity=DemandForecastFile::class, mappedBy="category", cascade={"remove"})
     */
    private $demandForecastFiles;

    public function __construct()
    {
        $this->salesFiles = new ArrayCollection();
        $this->demandForecastFiles = new ArrayCollection();
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

    public function getPurchaseUser(): ?User
    {
        return $this->purchase_user;
    }

    public function setPurchaseUser(?User $user_id): self
    {
        $this->purchase_user = $user_id;

        return $this;
    }

    public static function fromDto(CategoryDto $categoryDto): self
    {
        $category = new self();
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

    /**
     * @return Collection|DemandForecastFile[]
     */
    public function getDemandForecastFiles(): Collection
    {
        return $this->demandForecastFiles;
    }

    public function addDemandForecastFile(DemandForecastFile $demandForecastFile): self
    {
        if (!$this->demandForecastFiles->contains($demandForecastFile)) {
            $this->demandForecastFiles[] = $demandForecastFile;
            $demandForecastFile->setCategory($this);
        }

        return $this;
    }

    public function removeDemandForecastFile(DemandForecastFile $demandForecastFile): self
    {
        // set the owning side to null (unless already changed)
        if ($this->demandForecastFiles->removeElement($demandForecastFile)
            && $demandForecastFile->getCategory() === $this) {
            $demandForecastFile->setCategory(null);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
