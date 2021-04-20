<?php

namespace App\Entity;

use App\Model\PurchasePlanDto;
use App\Repository\PurchasePlanRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PurchasePlanRepository::class)
 */
class PurchasePlan
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
     * @ORM\Column(type="integer")
     */
    private $freqDelivery;

    /**
     * @ORM\Column(type="integer")
     */
    private $orderPoint;

    /**
     * @ORM\Column(type="integer")
     */
    private $reserve;

    /**
     * @ORM\Column(type="integer")
     */
    private $sizeOrder;

    /**
     * @ORM\Column(type="float")
     */
    private $totalCost;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="purchasePlans")
     * @ORM\JoinColumn(nullable=false)
     */
    private $purchase_user;

    /**
     * @ORM\OneToOne(targetEntity=DemandForecastFile::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $demandForecastFile;

    /**
     * @ORM\Column(type="integer")
     */
    private $serviceLevel;

    /**
     * @ORM\Column(type="float")
     */
    private $storageCost;

    /**
     * @ORM\Column(type="float")
     */
    private $productPrice;

    /**
     * @ORM\Column(type="float")
     */
    private $shippingCost;

    /**
     * @ORM\Column(type="integer")
     */
    private $timeShipping;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $delayedDeliveries;

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

    public function getOrderPoint(): ?int
    {
        return $this->orderPoint;
    }

    public function setOrderPoint(int $orderPoint): self
    {
        $this->orderPoint = $orderPoint;

        return $this;
    }

    public function getReserve(): ?int
    {
        return $this->reserve;
    }

    public function setReserve(int $reserve): self
    {
        $this->reserve = $reserve;

        return $this;
    }

    public function getSizeOrder(): ?int
    {
        return $this->sizeOrder;
    }

    public function setSizeOrder(int $sizeOrder): self
    {
        $this->sizeOrder = $sizeOrder;

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

    public function getDemandForecastFile(): ?DemandForecastFile
    {
        return $this->demandForecastFile;
    }

    public function setDemandForecastFile(DemandForecastFile $demandForecastFile): self
    {
        $this->demandForecastFile = $demandForecastFile;

        return $this;
    }

    public static function fromDto(User $user, PurchasePlanDto $plan, DemandForecastFile $file): self
    {
        $purchasePlan = new self();
        $purchasePlan->setPurchaseUser($user);
        $purchasePlan->setFilename($plan->filename);
        $purchasePlan->setDemandForecastFile($file);
        $purchasePlan->setServiceLevel($plan->service_level);
        $purchasePlan->setStorageCost($plan->storage_cost);
        $purchasePlan->setProductPrice($plan->product_price);
        $purchasePlan->setShippingCost($plan->shipping_costs);
        $purchasePlan->setTimeShipping($plan->time_shipping);
        if (isset($plan->delayed_deliveries)) {
            $purchasePlan->setDelayedDeliveries($plan->delayed_deliveries);
        }
        return $purchasePlan;
    }

    public function getFreqDelivery(): ?int
    {
        return $this->freqDelivery;
    }

    public function setFreqDelivery(int $freqDelivery): self
    {
        $this->freqDelivery = $freqDelivery;

        return $this;
    }

    public function getTotalCost(): ?float
    {
        return $this->totalCost;
    }

    public function setTotalCost(float $totalCost): self
    {
        $this->totalCost = $totalCost;

        return $this;
    }

    public function getServiceLevel(): ?int
    {
        return $this->serviceLevel;
    }

    public function setServiceLevel(int $serviceLevel): self
    {
        $this->serviceLevel = $serviceLevel;

        return $this;
    }

    public function getStorageCost(): ?float
    {
        return $this->storageCost;
    }

    public function setStorageCost(float $storageCost): self
    {
        $this->storageCost = $storageCost;

        return $this;
    }

    public function getProductPrice(): ?float
    {
        return $this->productPrice;
    }

    public function setProductPrice(float $productPrice): self
    {
        $this->productPrice = $productPrice;

        return $this;
    }

    public function getShippingCost(): ?float
    {
        return $this->shippingCost;
    }

    public function setShippingCost(float $shippingCost): self
    {
        $this->shippingCost = $shippingCost;

        return $this;
    }

    public function getTimeShipping(): ?int
    {
        return $this->timeShipping;
    }

    public function setTimeShipping(int $timeShipping): self
    {
        $this->timeShipping = $timeShipping;

        return $this;
    }

    public function getDelayedDeliveries(): ?int
    {
        return $this->delayedDeliveries;
    }

    public function setDelayedDeliveries(?int $delayedDeliveries): self
    {
        $this->delayedDeliveries = $delayedDeliveries;

        return $this;
    }
}
