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

    private const METHODS_TYPES = [
        1 => 'метод Хольта-Винтерса',
        2 => 'модель SARIMA',
    ];

    /**
     * @ORM\Column(type="smallint")
     */
    private $analysisMethod;

    /**
     * @ORM\OneToMany(targetEntity=PurchasePlan::class, mappedBy="demandForecastFile", orphanRemoval=true)
     */
    private $purchasePlans;

    /**
     * @ORM\Column(type="float")
     */
    private $rmse;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $interval;

    /**
     * @ORM\ManyToOne(targetEntity=SalesFile::class, inversedBy="demandForecastFiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $salesFile;

    public function __construct()
    {
        $this->purchasePlans = new ArrayCollection();
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
            $demandForecast->setSalesFile($file);
        } else {
            $demandForecast->setSalesFile($file);
        }
        $demandForecast->setAnalysisMethod($demandForecastFileDtoRequest->method);
        $demandForecast->setAnalysisField($demandForecastFileDtoRequest->column);
        $demandForecast->setForecastPeriod($demandForecastFileDtoRequest->period);
        $demandForecast->setInterval($demandForecastFileDtoRequest->freq);
        return $demandForecast;
    }

    public function getAccuracy(): ?float
    {
        return round($this->accuracy, 2);
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

    public function getAnalysisMethodFormatNumber(): ?int
    {
        return $this->analysisMethod;
    }

    public function getAnalysisMethodFormatString(): ?string
    {
        return self::METHODS_TYPES[$this->analysisMethod];
    }

    public function setAnalysisMethod(int $analysisMethod): self
    {
        $this->analysisMethod = $analysisMethod;

        return $this;
    }

    /**
     * @return Collection|PurchasePlan[]
     */
    public function getPurchasePlans(): Collection
    {
        return $this->purchasePlans;
    }

    public function addPurchasePlan(PurchasePlan $purchasePlan): self
    {
        if (!$this->purchasePlans->contains($purchasePlan)) {
            $this->purchasePlans[] = $purchasePlan;
            $purchasePlan->setDemandForecastFile($this);
        }

        return $this;
    }

    public function removePurchasePlan(PurchasePlan $purchasePlan): self
    {
        if ($this->purchasePlans->removeElement($purchasePlan)) {
            // set the owning side to null (unless already changed)
            if ($purchasePlan->getDemandForecastFile() === $this) {
                $purchasePlan->setDemandForecastFile(null);
            }
        }

        return $this;
    }

    public function getRmse(): ?float
    {
        return $this->rmse;
    }

    public function setRmse(float $rmse): self
    {
        $this->rmse = $rmse;

        return $this;
    }

    public function getInterval(): ?string
    {
        return $this->interval;
    }

    public function setInterval(string $interval): self
    {
        $this->interval = $interval;

        return $this;
    }

    public function __toString(): string
    {
        return $this->filename;
    }

    public function getSalesFile(): ?SalesFile
    {
        return $this->salesFile;
    }

    public function setSalesFile(?SalesFile $salesFile): self
    {
        $this->salesFile = $salesFile;

        return $this;
    }
}
