<?php

namespace App\Entity;

use App\Model\SalesFileDto;
use App\Repository\CategoryRepository;
use App\Repository\SalesFileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=SalesFileRepository::class)
 */
class SalesFile
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
    private $filename;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="salesFiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="salesFiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $purchase_user;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     */
    private $separator;

    /**
     * @ORM\OneToMany(targetEntity=DemandForecastFile::class, mappedBy="salesFile", orphanRemoval=true)
     */
    private $demandForecastFiles;

    /**
     * @ORM\Column(type="boolean")
     */
    private $createdByCategory;

    public function __construct()
    {
        $this->demandForecastFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public static function fromDto(SalesFileDto $salesFileDto, CategoryRepository $categoryRepository): self
    {
        // Поиск категории по id
        $category = $categoryRepository->find($salesFileDto->category_id);
        // Содание объекта файл продаж
        $salesFile = new self();
        $salesFile->setCategory($category);
        $salesFile->setFilename($salesFileDto->filename);
        $salesFile->setSeparator($salesFileDto->separator);
        return $salesFile;
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

    public function __toString(): string
    {
        return $this->getFilename();
    }

    public function getSeparator(): ?string
    {
        return $this->separator;
    }

    public function setSeparator(?string $separator): self
    {
        $this->separator = $separator;

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
            $demandForecastFile->setSalesFile($this);
        }

        return $this;
    }

    public function removeDemandForecastFile(DemandForecastFile $demandForecastFile): self
    {
        if ($this->demandForecastFiles->removeElement($demandForecastFile)) {
            // set the owning side to null (unless already changed)
            if ($demandForecastFile->getSalesFile() === $this) {
                $demandForecastFile->setSalesFile(null);
            }
        }

        return $this;
    }

    public function getCreatedByCategory(): ?bool
    {
        return $this->createdByCategory;
    }

    public function setCreatedByCategory(?bool $createdByCategory): self
    {
        $this->createdByCategory = $createdByCategory;

        return $this;
    }
}
