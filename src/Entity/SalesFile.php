<?php

namespace App\Entity;

use App\Model\Request\SalesFileDtoRequest;
use App\Repository\CategoryRepository;
use App\Repository\SalesFileRepository;
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
     * @ORM\Column(type="datetime")
     * @Assert\DateTime()
     */
    private $editAt;

    /**
     * @ORM\ManyToOne(targetEntity=Category::class, inversedBy="salesFiles")
     * @ORM\JoinColumn(nullable=false)
     */
    private $category;

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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public static function fromDto(SalesFileDtoRequest $salesFileDto, CategoryRepository $categoryRepository): self
    {
        // Поиск категории по id
        $category = $categoryRepository->find($salesFileDto->category_id);
        // Содание объекта файл продаж
        $salesFile = new self();
        $salesFile->setCategory($category);
        $salesFile->setFilename($salesFileDto->filename);
        return $salesFile;
    }
}
