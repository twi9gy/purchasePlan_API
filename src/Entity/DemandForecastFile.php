<?php

namespace App\Entity;

use App\Model\DemandForecastFileDto;
use App\Repository\DemandForecastFileRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=DemandForecastFileRepository::class)
 *
 * @UniqueEntity(
 *     fields={"filename"},
 *     message="Название файла должно быть уникальным"
 * )
 */
class DemandForecastFile
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
     * @ORM\Column(type="datetime")
     */
    private $editAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $analysisMethod;

    /**
     * @ORM\ManyToMany(targetEntity=SalesFile::class, inversedBy="demandForecastFiles")
     */
    private $salesFiles;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="demandForecastFiles")
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="demandForecastFiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $purchase_user;

    /**
     * @ORM\Column(type="float", nullable=true)
     */
    private $accuracy;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $analysisField;

    /**
     * @ORM\Column(type="integer")
     */
    private $forecastPeriod;

    public function __construct()
    {
        $this->salesFiles = new ArrayCollection();
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

    public function getEditAt(): ?\DateTimeInterface
    {
        return $this->editAt;
    }

    public function setEditAt(\DateTimeInterface $editAt): self
    {
        $this->editAt = $editAt;

        return $this;
    }

    public function getAnalysisMethod(): ?string
    {
        return $this->analysisMethod;
    }

    public function setAnalysisMethod(string $analysisMethod): self
    {
        $this->analysisMethod = $analysisMethod;

        return $this;
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
        }

        return $this;
    }

    public function removeSalesFile(SalesFile $salesFile): self
    {
        $this->salesFiles->removeElement($salesFile);

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

    public function getPurchaseUser(): ?User
    {
        return $this->purchase_user;
    }

    public function setPurchaseUser(?User $user_id): self
    {
        $this->purchase_user = $user_id;

        return $this;
    }

    public static function fromDto(
        User $user,
        DemandForecastFileDto $demandForecastFileDtoRequest,
        Category $category = null,
        SalesFile $file = null
    ): self {
        $demandForecast = new self();
        $demandForecast->setPurchaseUser($user);
        $demandForecast->setFilename($demandForecastFileDtoRequest->filename);
        if ($demandForecastFileDtoRequest->object_analysis === 'category') {
            $demandForecast->setCategory($category);
        } else {
            $demandForecast->salesFiles->add($file);
        }
        $demandForecast->setAnalysisMethod($demandForecastFileDtoRequest->method);
        $demandForecast->setAnalysisField($demandForecastFileDtoRequest->column);
        $demandForecast->setForecastPeriod($demandForecastFileDtoRequest->period);
        return $demandForecast;
    }

    public function getAccuracy(): ?float
    {
        return $this->accuracy;
    }

    public function setAccuracy(?float $accuracy): self
    {
        $this->accuracy = $accuracy;

        return $this;
    }

    public function getAnalysisField(): ?string
    {
        return $this->analysisField;
    }

    public function setAnalysisField(?string $analysisField): self
    {
        $this->analysisField = $analysisField;

        return $this;
    }

    public function getForecastPeriod(): ?int
    {
        return $this->forecastPeriod;
    }

    public function setForecastPeriod(int $forecastPeriod): self
    {
        $this->forecastPeriod = $forecastPeriod;

        return $this;
    }
}
