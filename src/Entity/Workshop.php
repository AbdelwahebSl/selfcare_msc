<?php

namespace App\Entity;

use App\Repository\WorkshopRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Translatable;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\TranslationEntity(class="App\Entity\Translation\WorkshopTranslation")
 * @ORM\Entity(repositoryClass=WorkshopRepository::class)
 * @ORM\Table(name="workshop")
 * @Vich\Uploadable
 */
class Workshop implements Translatable
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Theme")
     * @ORM\JoinColumn(name="theme_id", referencedColumnName="id")
     */
    private $theme;

    /**
     *  @Gedmo\Translatable
     * @ORM\Column(type="string", length=100)
     */
    private $name;

    /**
     *  @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     *  @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $videoTitle;


    /**
     * @ORM\Column(type="string", length=150,name="workshop_abstract", nullable=true)
     */
    private $workshopAbstract;

    /**
     * @ORM\Column(type="integer",name="objective_count")
     */
    private $objectiveCount;

    /**
     * @ORM\Column(type="integer",name="workshop_duration")
     */
    private $workshopDuration;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=50,name="duration_unit")
     */
    private $durationUnit;

    /**
     * @ORM\Column(type="integer",name="workshop_type")
     */
    private $workshopType;


    /**
     * @ORM\Column(type="string", length=50)
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=100,name="workshop_establishment",nullable=true)
     */
    private $workshopEstablishment;

    /**
     * @ORM\Column(type="boolean",name="workshop_status")
     */
    private $workshopStatus;

    /**
     * @ORM\Column(type="integer",name="workshop_order")
     */
    private $workshopOrder;

    /**
     *
     * @Vich\UploadableField(mapping="workshop_image", fileNameProperty="imageName", size="imageSize")
     *
     * @var File|null
     */
    private $imageFile;


    /**
     * @ORM\Column(type="string",name="image_name")
     *
     * @var string|null
     */
    private $imageName;

    /**
     * @ORM\Column(type="integer",name="image_size")
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

    /**
     * @ORM\Column(type="integer",name="consulted_count",nullable="true")
     */
    private $consultedCount;

    /**
     * @ORM\Column(type="integer",name="purchased_count",nullable="true")
     */
    private $purchasedCount;

    /**
     * @ORM\Column(type="json",name="expiration_date", nullable="true")
     */
    private $expirationDate;

    /**
     * @ORM\Column(type="string",nullable="true")
     */
    private $rate;

    /**
     * @ORM\Column(type="string",nullable="true")
     */
    private $quizLink;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->consultedCount = 0;
        $this->purchasedCount = 0;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDurationUnit()
    {
        return $this->durationUnit;
    }

    /**
     * @param mixed $durationUnit
     */
    public function setDurationUnit($durationUnit): void
    {
        $this->durationUnit = $durationUnit;
    }

    /**
     * @return mixed
     */
    public function getVideoTitle()
    {
        return $this->videoTitle;
    }

    /**
     * @param mixed $videoTitle
     */
    public function setVideoTitle($videoTitle): void
    {
        $this->videoTitle = $videoTitle;
    }




    public function getWorkshopStatus(): ?bool
    {
        return $this->workshopStatus;
    }

    public function setWorkshopStatus(bool $workshopStatus): self
    {
        $this->workshopStatus = $workshopStatus;

        return $this;
    }

    public function getWorkshopOrder(): ?int
    {
        return $this->workshopOrder;
    }

    public function setWorkshopOrder(int $workshopOrder): self
    {
        $this->workshopOrder = $workshopOrder;

        return $this;
    }


    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }


    public function getWorkshopAbstract(): ?string
    {
        return $this->workshopAbstract;
    }

    public function setWorkshopAbstract(string $workshopAbstract): self
    {
        $this->workshopAbstract = $workshopAbstract;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWorkshopDuration()
    {
        return $this->workshopDuration;
    }

    /**
     * @param mixed $workshopDuration
     */
    public function setWorkshopDuration($workshopDuration): void
    {
        $this->workshopDuration = $workshopDuration;
    }





    public function getWorkshopEstablishment(): ?string
    {
        return $this->workshopEstablishment;
    }

    public function setWorkshopEstablishment(?string $workshopEstablishment): self
    {
        $this->workshopEstablishment = $workshopEstablishment;

        return $this;
    }

    public function getTheme(): ?Theme
    {
        return $this->theme;
    }

    public function setTheme(?Theme $theme): self
    {
        $this->theme = $theme;

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

    public function getObjectiveCount(): ?int
    {
        return $this->objectiveCount;
    }

    public function setObjectiveCount(int $objectiveCount): self
    {
        $this->objectiveCount = $objectiveCount;

        return $this;
    }

    public function getWorkshopType(): ?int
    {
        return $this->workshopType;
    }

    public function setWorkshopType(int $workshopType): self
    {
        $this->workshopType = $workshopType;

        return $this;
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

    public function getConsultedCount(): ?int
    {
        return $this->consultedCount;
    }

    public function setConsultedCount(?int $consultedCount): self
    {
        $this->consultedCount = $consultedCount;

        return $this;
    }

    public function getPurchasedCount(): ?int
    {
        return $this->purchasedCount;
    }

    public function setPurchasedCount(?int $purchasedCount): self
    {
        $this->purchasedCount = $purchasedCount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @param mixed $expirationDate
     */
    public function setExpirationDate($expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    /**
     * @return mixed
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param mixed $rate
     */
    public function setRate($rate): void
    {
        $this->rate = $rate;
    }




    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getQuizLink()
    {
        return $this->quizLink;
    }

    /**
     * @param mixed $quizLink
     */
    public function setQuizLink($quizLink): void
    {
        $this->quizLink = $quizLink;
    }



    public function setTranslatableLocale(string $locale): void
    {
        $this->locale = $locale;
    }
}
