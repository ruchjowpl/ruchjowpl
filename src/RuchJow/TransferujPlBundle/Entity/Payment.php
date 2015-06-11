<?php

namespace RuchJow\TransferujPlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class Payment
 *
 * @ORM\Entity(repositoryClass="RuchJow\TransferujPlBundle\Entity\PaymentRepository")
 * @ORM\Table(name="fr_transferuj_pl_payments")
 */
class Payment {

    const ERROR_NONE = 0;
    const ERROR_OVERPAY = 1;
    const ERROR_SURCHARGE = -1;

    const ERROR_NONE_NAME = 'none';
    const ERROR_OVERPAY_NAME = 'overpay';
    const ERROR_SURCHARGE_NAME = 'surcharge';

    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     */
    protected $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $date;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128, nullable=false)
     */
    protected $crc;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=12, scale=2, nullable=false)
     */
    protected $amount;

    /**
     * @var float
     *
     * @ORM\Column(type="decimal", precision=12, scale=2, nullable=false)
     */
    protected $paid;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $status;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", nullable=false)
     */
    protected $error;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $payersEmail;


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $type;

     /**
     * @var boolean
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $isSentEmail=false;


    /**
     * Set id
     *
     * @param string $id
     * @return Payment
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Payment
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set crc
     *
     * @param string $crc
     * @return Payment
     */
    public function setCrc($crc)
    {
        $this->crc = $crc;

        $data = json_decode($crc, true);
        if (
            $data
            && isset($data['type'])
            && $data['type']
            && is_string($data['type'])
        ) {
            $this->setType($data['type']);
        }

        return $this;
    }

    /**
     * Get crc
     *
     * @return string
     */
    public function getCrc()
    {
        return $this->crc;
    }

    /**
     * Set amount
     *
     * @param string $amount
     * @return Payment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set paid
     *
     * @param string $paid
     * @return Payment
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Get paid
     *
     * @return string
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Set status
     *
     * @param string $status
     * @return Payment
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function isPayed()
    {
        return $this->status === 'TRUE';
    }

    /**
     * Set error
     *
     * @param integer $error
     * @return Payment
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get error
     *
     * @return integer
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set payersEmail
     *
     * @param string $payersEmail
     * @return Payment
     */
    public function setPayersEmail($payersEmail)
    {
        $this->payersEmail = $payersEmail;

        return $this;
    }

    /**
     * Get payersEmail
     *
     * @return string
     */
    public function getPayersEmail()
    {
        return $this->payersEmail;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }



    /**
     * Set isSentEmail
     *
     * @param boolean $isSentEmail
     * @return Payment
     */
    public function setIsSentEmail($isSentEmail)
    {
        $this->isSentEmail = $isSentEmail;

        return $this;
    }

    /**
     * Get isSentEmail
     *
     * @return boolean
     */
    public function getIsSentEmail()
    {
        return $this->isSentEmail;
    }
}
