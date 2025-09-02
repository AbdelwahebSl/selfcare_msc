<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PaymentFileRepository;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @ORM\Entity(repositoryClass=PaymentFileRepository::class)
 * @ORM\Entity
 * @ORM\Table(name="msc_file")
 * @Vich\Uploadable
 */
class CartFile
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $fileName;

    /**
     * @ORM\Column(type="integer",name="file_size")
     *
     * @var int|null
     */
    private $fileSize;

    /**
     * @Vich\UploadableField(mapping="payment_file", fileNameProperty="fileName", size="fileSize")
     * @var File
     */
    private $file;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    private $description;


    /**
     * @ORM\Column (type="string", name="approval_user", nullable=true)
     */
    private $approvalUser;

    /**
     * @var string
     *
     * @ORM\Column(type="string",name="payed_at",nullable=true)
     */
    private $payedAt;


    /**
     * @ORM\Column(type="string",name="file_status", length=255, nullable=true)
     */
    private $status;




    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getFileName():?string
    {
        return $this->fileName;
    }

    /**
     * @param mixed $fileName
     */
    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getFile() {
        return $this->file;
    }


    /**
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $createdAt
     */
    public function setCreatedAt(\DateTime $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getPayedAt(): string
    {
        return $this->payedAt;
    }

    /**
     * @param string $payedAt
     */
    public function setPayedAt(string $payedAt): void
    {
        $this->payedAt = $payedAt;
    }





    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status): void
    {
        $this->status = $status;
    }


    public function setFile(?File $file = null): void
    {
        $this->file = $file;

        if (null !== $file) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    /**
     * @return Collection<int, WorkshopCart>
     */
    public function getWorkshopCarts(): Collection
    {
        return $this->workshopCarts;
    }

    /**
     * @return int|null
     */
    public function getFileSize(): ?int
    {
        return $this->fileSize;
    }

    /**
     * @param int|null $fileSize
     */
    public function setFileSize(?int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    /**
     * @return mixed
     */
    public function getApprovalUser()
    {
        return $this->approvalUser;
    }

    /**
     * @param mixed $approvalUser
     */
    public function setApprovalUser($approvalUser): void
    {
        $this->approvalUser = $approvalUser;
    }





}
