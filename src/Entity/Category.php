<?php

namespace App\Entity;

use App\Repository\CategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;


/**
 * @Vich\Uploadable
 */
#[ORM\Entity(repositoryClass: CategoryRepository::class)]
//#[Vich\Uploadable]

class Category
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $CategoryName;

    /**
     * @Vich\UploadableField(mapping="category_image", fileNameProperty="CategoryImage")
     * @var File|null
     */
//    #[Vich\UploadableField(mapping: "category_image", fileNameProperty: "CategoryImage")]
    private $imageFile;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $CategoryImage;

    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Product::class)]
    private $Product;

    public function __toString()
    {
        return $this->CategoryName;
    }


    public function __construct()
    {
        $this->Product = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCategoryName(): ?string
    {
        return $this->CategoryName;
    }

    public function setCategoryName(string $CategoryName): self
    {
        $this->CategoryName = $CategoryName;

        return $this;
    }

    public function getCategoryImage(): ?string
    {
        return $this->CategoryImage;
    }

    public function setCategoryImage(?string $CategoryImage): self
    {
        $this->CategoryImage = $CategoryImage;

        return $this;
    }

    /**
     * @param File|UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null)
    {
        $this->imageFile = $imageFile;

//        if (null !== $imageFile) {
//            // It is required that at least one field changes if you are using doctrine
//            // otherwise the event listeners won't be called and the file is lost
//            $this->setUpdatedAt(new \DateTimeImmutable);
//        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    /**
     * @return Collection|Product[]
     */
    public function getProduct(): Collection
    {
        return $this->Product;
    }

    public function addProduct(Product $product): self
    {
        if (!$this->Product->contains($product)) {
            $this->Product[] = $product;
            $product->setCategory($this);
        }

        return $this;
    }

    public function removeProduct(Product $product): self
    {
        if ($this->Product->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getCategory() === $this) {
                $product->setCategory(null);
            }
        }

        return $this;
    }
}
