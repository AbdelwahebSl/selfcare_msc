<?php

namespace App\Entity;

use App\Repository\WorkShopObjectivesRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;


/**
 * @ORM\Entity(repositoryClass=WorkShopObjectivesRepository::class)
 * * @Gedmo\TranslationEntity(class="App\Entity\Translation\WorkshopTranslation")
 * @ORM\Table(name="workshop_objective")
 */
class WorkShopObjectives  implements Translatable
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
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="boolean",name="objective_status")
     */
    private $objectiveStatus;

    /**
     * @ORM\Column(type="integer",name="objective_order")
     */
    private $objectiveOrder;

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
     * @Gedmo\Locale
     */
    private $locale;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }
    public function setTranslatableLocale(string $locale): void
    {
        $this->locale = $locale;
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

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getObjectiveStatus(): ?bool
    {
        return $this->objectiveStatus;
    }

    public function setObjectiveStatus(bool $objectiveStatus): self
    {
        $this->objectiveStatus = $objectiveStatus;

        return $this;
    }

    public function getObjectiveOrder(): ?int
    {
        return $this->objectiveOrder;
    }

    public function setObjectiveOrder(int $objectiveOrder): self
    {
        $this->objectiveOrder = $objectiveOrder;

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
