<?php

namespace App\Entity;

use App\Repository\WorkShopSupportRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\TranslationEntity(class="App\Entity\Translation\WorkshopTranslation")
 * @ORM\Entity(repositoryClass=WorkShopSupportRepository::class)
 * @ORM\Table(name="workshop_support")
 * @Vich\Uploadable
 */
class WorkShopSupport implements Translatable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Workshop")
     * @ORM\JoinColumn(name="workshop_id", referencedColumnName="id")
     */
    private $workshop;

    /**
     * @ORM\Column(type="string", length=255)
     *
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true,name="support_description")
     */
    private $supportDescription;

    /**
     * @ORM\Column(type="string", length=50,name="support_type")
     */
    private $supportType;

    /**
     * @ORM\Column(type="string", length=255,name="support_link", nullable=true)
     */
    private $supportLink;

    /**
     * @ORM\Column(type="integer",name="support_order")
     */
    private $supportOrder;

    /**
     * @ORM\Column(type="boolean",name="support_status")
     */
    private $supportStatus;


    /**
     *
     * @Vich\UploadableField(mapping="workshop_image", fileNameProperty="imageName", size="imageSize")
     *
     * @var File|null
     */
    private $imageFile;


    /**
     * @ORM\Column(type="string",name="image_name",nullable="true")
     * @Gedmo\Translatable
     *
     * @var string|null
     */
    private $imageName;

    /**
     * @ORM\Column(type="integer",name="image_size",nullable="true")
     *
     * @var int|null
     */
    private $imageSize;

    /**
     * @ORM\Column(type="datetime",name="updated_at", nullable=true)
     *
     * @var \DateTimeInterface|null
     */
    private $updatedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime",name="created_at")
     */
    private $createdAt;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
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

    public function getSupportDescription(): ?string
    {
        return $this->supportDescription;
    }

    public function setSupportDescription(?string $supportDescription): self
    {
        $this->supportDescription = $supportDescription;

        return $this;
    }

    public function getSupportType(): ?string
    {
        return $this->supportType;
    }

    public function setSupportType(string $supportType): self
    {
        $this->supportType = $supportType;

        return $this;
    }

    public function getSupportLink(): ?string
    {
        return $this->supportLink;
    }

    public function setSupportLink(?string $supportLink): self
    {
        $this->supportLink = $supportLink;

        return $this;
    }

    public function getSupportOrder(): ?int
    {
        return $this->supportOrder;
    }

    public function setSupportOrder(int $supportOrder): self
    {
        $this->supportOrder = $supportOrder;

        return $this;
    }

    public function getSupportStatus(): ?bool
    {
        return $this->supportStatus;
    }

    public function setSupportStatus(bool $supportStatus): self
    {
        $this->supportStatus = $supportStatus;

        return $this;
    }

    public function getWorkshop(): ?Workshop
    {
        return $this->workshop;
    }

    public function setWorkshop(?Workshop $workshop): self
    {
        $this->workshop = $workshop;

        return $this;
    }


    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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

}
