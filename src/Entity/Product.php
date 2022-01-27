<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;


/**
 * @Vich\Uploadable
 * @ORM\Table(name="product", indexes={@ORM\Index(columns={"product_name", "product_description"}, flags={"fulltext"})})
 */
#[ORM\Entity(repositoryClass: ProductRepository::class)]
#[Entity, HasLifecycleCallbacks]
//#[Table]
//#[UniqueConstraint(name: "product", columns=["product_name", "product_description"])]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $productName;

    #[ORM\Column(type: 'float')]
    private $productPrice;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $productImage;

    #[ORM\Column(type: 'float', nullable: true)]
    private $productTaxe;

    #[ORM\Column(type: 'text', nullable: true)]
    private $productDescription;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="product_image", fileNameProperty="productImage")
     *
     * @var File|null
     */
//    #[Vich\UploadableField(mapping: 'product_image', fileNameProperty: 'productImage')]
    private $imageFile;

    #[ORM\Column(type: 'datetime_immutable', options: [ "default" => "CURRENT_TIMESTAMP" ])]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable', options: [ "default" => "CURRENT_TIMESTAMP" ])]
    private $updatedAt;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'Product')]
    #[Assert\NotBlanck]
    private $category;

    #[ORM\Column(type: 'boolean', options: [ "default" => 0 ])]
    private $onSale;


    #[ORM\ManyToMany(targetEntity: Color::class, inversedBy: 'products')]
    private $color;

    #[ORM\ManyToMany(targetEntity: Size::class, inversedBy: 'products')]
    private $size;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: Details::class)]
    private $Details;



    public function __construct()
    {
        $this->color = new ArrayCollection();
        $this->size = new ArrayCollection();
        $this->Details = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProductName(): ?string
    {
        return $this->productName;
    }

    public function setProductName(?string $productName): self
    {
        $this->productName = $productName;

        return $this;
    }

    public function getProductPrice(): ?float
    {
        return $this->productPrice;
    }

    public function setProductPrice(?float $productPrice): self
    {
        $this->productPrice = $productPrice;

        return $this;
    }

    public function getProductImage(): ?string
    {
        return $this->productImage;
    }

    public function setProductImage(?string $productImage): self
    {
        $this->productImage = $productImage;

        return $this;
    }

    public function getProductTaxe(): ?float
    {
        return $this->productTaxe;
    }

    public function setProductTaxe(?float $productTaxe): self
    {
        $this->productTaxe = $productTaxe;

        return $this;
    }

    public function getProductDescription(): ?string
    {
        return $this->productDescription;
    }

    public function setProductDescription(?string $productDescription): self
    {
        $this->productDescription = $productDescription;

        return $this;
    }

    /**
     * @param File|UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null)
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->setUpdatedAt(new \DateTimeImmutable());
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function updateTimestamps()
    {
        if ($this->getCreatedAt()===null)
        {
            $this->setCreatedAt(new \DateTimeImmutable);
        }
        $this->setUpdatedAt(new \DateTimeImmutable);
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

    public function getOnSale(): ?bool
    {
        return $this->onSale;
    }

    public function setOnSale(?bool $onSale): self
    {
        $this->onSale = $onSale;

        return $this;
    }



    /**
     * @return Collection|Color[]
     */
    public function getColor(): Collection
    {
        return $this->color;
    }

    public function addColor(Color $color): self
    {
        if (!$this->color->contains($color)) {
            $this->color[] = $color;
        }

        return $this;
    }

    public function removeColor(Color $color): self
    {
        $this->color->removeElement($color);

        return $this;
    }

    /**
     * @return Collection|Size[]
     */
    public function getSize(): Collection
    {
        return $this->size;
    }

    public function addSize(Size $size): self
    {
        if (!$this->size->contains($size)) {
            $this->size[] = $size;
        }

        return $this;
    }

    public function removeSize(Size $size): self
    {
        $this->size->removeElement($size);

        return $this;
    }

    /**
     * @return Collection|Details[]
     */
    public function getDetails(): Collection
    {
        return $this->Details;
    }

    public function addDetail(Details $detail): self
    {
        if (!$this->Details->contains($detail)) {
            $this->Details[] = $detail;
            $detail->setProduct($this);
        }

        return $this;
    }

    public function removeDetail(Details $detail): self
    {
        if ($this->Details->removeElement($detail)) {
            // set the owning side to null (unless already changed)
            if ($detail->getProduct() === $this) {
                $detail->setProduct(null);
            }
        }

        return $this;
    }








}
