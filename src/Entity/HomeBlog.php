<?php

namespace App\Entity;

use App\Repository\HomeBlogRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable
 */
#[ORM\Entity(repositoryClass: HomeBlogRepository::class)]
class HomeBlog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $homeTitle;

    #[ORM\Column(type: 'string', length: 255)]
    private $homeDescription;

    #[ORM\Column(type: 'string', length: 255)]
    private $homeContent;

    #[ORM\Column(type: 'string', length: 255)]
    private $homeImage;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="homeBlog_image", fileNameProperty="homeImage")
     *
     * @var File|null
     */
//    #[Vich\UploadableField(mapping: 'product_image', fileNameProperty: 'productImage')]
    private $imageFile;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHomeTitle(): ?string
    {
        return $this->homeTitle;
    }

    public function setHomeTitle(string $homeTitle): self
    {
        $this->homeTitle = $homeTitle;

        return $this;
    }

    public function getHomeDescription(): ?string
    {
        return $this->homeDescription;
    }

    public function setHomeDescription(string $homeDescription): self
    {
        $this->homeDescription = $homeDescription;

        return $this;
    }

    public function getHomeContent(): ?string
    {
        return $this->homeContent;
    }

    public function setHomeContent(string $homeContent): self
    {
        $this->homeContent = $homeContent;

        return $this;
    }

    public function getHomeImage(): ?string
    {
        return $this->homeImage;
    }

    public function setHomeImage(string $homeImage): self
    {
        $this->homeImage = $homeImage;

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
}
