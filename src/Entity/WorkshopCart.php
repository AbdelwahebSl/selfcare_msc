<?php

namespace App\Entity;

use App\Repository\WorkshopCartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;


/**
 * @ORM\Entity(repositoryClass=WorkshopCartRepository::class)
 * @ORM\Table(name="workshop_cart")
 */
class WorkshopCart
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;


    /**
     * @ORM\ManyToOne(targetEntity="SelfcareUser")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $selfcareUer;

    /**
     * @ORM\ManyToOne(targetEntity="Workshop")
     * @ORM\JoinColumn(name="workshop_id", referencedColumnName="id")
     */
    private $workshop;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime",name="created_at")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime",name="updated_at")
     *
     * @var \DateTimeInterface|null
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string",name="payment_mode",nullable=true)
     */
    private $paymentMode;

    /**
     * @ORM\Column(type="string",name="payment_amount",nullable=true)
     */
    private $paymentAmount;

    /**
     * @var string
     *
     * @ORM\Column(type="string",name="payed_at",nullable=true)
     */
    private $payedAt;



    /**
     * @ORM\Column(type="string", name="order_id_smt",nullable=true)
     */
    private $orderIdSMT;


    /**
     * @ORM\Column(type="string",name="payment_bank",nullable=true)
     */
    private $paymentBank;

    /**
     * @ORM\Column(type="string",name="transaction_id",nullable=true)
     */
    private $paymentTransactionId;
    /**
     * @ORM\Column(type="string",name="payment_authorization",nullable=true)
     */
    private $paymentAuthorization;

    /**
     * @ORM\Column(type="string",name="payed_at_smt",nullable=true)
     */
    private $smtPayedAt;

    /**
     * @var string
     *
     * @ORM\Column(type="string", name="payment_status")
     */
    private $status;


    /**
     * @ORM\Column (type="string", name="expired", nullable=true)
     */
    private $expired;

    /**
     * @ORM\Column (type="datetime", name="expiration_date", nullable=true)
     */
    private $expirationDate;

    /**
     * @ORM\Column (type="boolean",  nullable=true)
     */
    private $isFree;

    /**
     * @ORM\ManyToOne(targetEntity=CartFile::class)
     */
    private $file;


    /**
     * @ORM\Column (type="boolean",  nullable=true)
     */
    private $readed;

    /**
     * @ORM\Column (type="boolean",  nullable=true)
     */
    private $quizPassed;


    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getPaymentMode(): ?string
    {
        return $this->paymentMode;
    }

    public function setPaymentMode(?string $paymentMode): self
    {
        $this->paymentMode = $paymentMode;

        return $this;
    }

    public function getPaymentAmount(): ?string
    {
        return $this->paymentAmount;
    }

    public function setPaymentAmount(?string $paymentAmount): self
    {
        $this->paymentAmount = $paymentAmount;

        return $this;
    }

    public function getPayedAt(): ?string
    {
        return $this->payedAt;
    }

    public function setPayedAt(?string $payedAt): self
    {
        $this->payedAt = $payedAt;

        return $this;
    }

    public function getOrderIdSMT(): ?string
    {
        return $this->orderIdSMT;
    }

    public function setOrderIdSMT(?string $orderIdSMT): self
    {
        $this->orderIdSMT = $orderIdSMT;

        return $this;
    }

    public function getPaymentBank(): ?string
    {
        return $this->paymentBank;
    }

    public function setPaymentBank(?string $paymentBank): self
    {
        $this->paymentBank = $paymentBank;

        return $this;
    }

    public function getPaymentTransactionId(): ?string
    {
        return $this->paymentTransactionId;
    }

    public function setPaymentTransactionId(?string $paymentTransactionId): self
    {
        $this->paymentTransactionId = $paymentTransactionId;

        return $this;
    }

    public function getPaymentAuthorization(): ?string
    {
        return $this->paymentAuthorization;
    }

    public function setPaymentAuthorization(?string $paymentAuthorization): self
    {
        $this->paymentAuthorization = $paymentAuthorization;

        return $this;
    }

    public function getSmtPayedAt(): ?string
    {
        return $this->smtPayedAt;
    }

    public function setSmtPayedAt(?string $smtPayedAt): self
    {
        $this->smtPayedAt = $smtPayedAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

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

    public function getSelfcareUer(): ?SelfcareUser
    {
        return $this->selfcareUer;
    }

    public function setSelfcareUer(?SelfcareUser $selfcareUer): self
    {
        $this->selfcareUer = $selfcareUer;

        return $this;
    }


    public function getExpired(): ?bool
    {
        return $this->expired;
    }


    public function setExpired($expired): void
    {
        $this->expired = $expired;
    }


    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    public function setExpirationDate($expirationDate): void
    {
        $this->expirationDate = $expirationDate;
    }

    /**
     * @return Collection<int, CartFile>
     */
    public function getFileName(): Collection
    {
        return $this->fileName;
    }


    public function getFile(): ?CartFile
    {
        return $this->file;
    }

    public function setFile(?CartFile $file): self
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsFree()
    {
        return $this->isFree;
    }

    /**
     * @param mixed $isFree
     */
    public function setIsFree($isFree): void
    {
        $this->isFree = $isFree;
    }

    /**
     * @return mixed
     */
    public function getReaded()
    {
        return $this->readed;
    }

    /**
     * @param mixed $readed
     */
    public function setReaded($readed): void
    {
        $this->readed = $readed;
    }

    /**
     * @return mixed
     */
    public function getQuizPassed()
    {
        return $this->quizPassed;
    }

    /**
     * @param mixed $quizPassed
     */
    public function setQuizPassed($quizPassed): void
    {
        $this->quizPassed = $quizPassed;
    }




}
