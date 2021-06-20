<?php

namespace App\Entity;

use App\Model\UserDto;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="purchase_plan_user")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $companyName;

    /**
     * @ORM\OneToMany(targetEntity=Category::class, mappedBy="purchase_user")
     */
    private $categories;

    /**
     * @ORM\OneToMany(targetEntity=DemandForecastFile::class, mappedBy="purchase_user", orphanRemoval=true)
     */
    private $demandForecastFiles;

    /**
     * @ORM\OneToMany(targetEntity=PurchasePlan::class, mappedBy="purchase_user", orphanRemoval=true)
     */
    private $purchasePlans;

    /**
     * @ORM\OneToMany(targetEntity=SalesFile::class, mappedBy="purchase_user", orphanRemoval=true)
     */
    private $salesFiles;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
        $this->demandForecastFiles = new ArrayCollection();
        $this->purchasePlans = new ArrayCollection();
        $this->salesFiles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @return string
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @return array
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @return string|null
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function setCompanyName(string $companyName): self
    {
        $this->companyName = $companyName;

        return $this;
    }

    public static function fromDto(UserDto $userDto): self
    {
        $user = new self();
        $user->setEmail($userDto->email);
        $user->setRoles(["ROLE_USER"]);
        $user->setPassword($userDto->password);
        $user->setCompanyName($userDto->company_name);
        return $user;
    }

    /**
     * @return Collection|Category[]
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function addCategory(Category $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories[] = $category;
            $category->setPurchaseUser($this);
        }

        return $this;
    }

    public function removeCategory(Category $category): self
    {
        // set the owning side to null (unless already changed)
        if ($this->categories->removeElement($category) && $category->getPurchaseUser() === $this) {
            $category->setPurchaseUser(null);
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
            $demandForecastFile->setPurchaseUser($this);
        }

        return $this;
    }

    public function removeDemandForecastFile(DemandForecastFile $demandForecastFile): self
    {
        // set the owning side to null (unless already changed)
        if ($this->demandForecastFiles->removeElement($demandForecastFile)
            && $demandForecastFile->getPurchaseUser() === $this) {
            $demandForecastFile->setPurchaseUser(null);
        }

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
            $purchasePlan->setPurchaseUser($this);
        }

        return $this;
    }

    public function removePurchasePlan(PurchasePlan $purchasePlan): self
    {
        // set the owning side to null (unless already changed)
        if ($this->purchasePlans->removeElement($purchasePlan) && $purchasePlan->setPurchaseUser() === $this) {
            $purchasePlan->setPurchaseUser(null);
        }

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
            $salesFile->setPurchaseUser($this);
        }

        return $this;
    }

    public function removeSalesFile(SalesFile $salesFile): self
    {
        // set the owning side to null (unless already changed)
        if ($this->salesFiles->removeElement($salesFile) && $salesFile->setPurchaseUser() === $this) {
            $salesFile->setPurchaseUser(null);
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->getEmail();
    }
}
