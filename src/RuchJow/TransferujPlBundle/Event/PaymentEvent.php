<?php

namespace RuchJow\TransferujPlBundle\Event;

use RuchJow\TransferujPlBundle\Entity\Payment;
use Symfony\Component\EventDispatcher\Event;


class PaymentEvent extends Event
{

    /**
     * @var Payment
     */
    protected $payment;

    /**
     * @param Payment $payment
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * @return Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param Payment $payment
     *
     * @return $this
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;

        return $this;
    }
}