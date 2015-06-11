<?php

namespace RuchJow\TransferujPlBundle\Event;

use RuchJow\TransferujPlBundle\Entity\Payment;

/**
 * Created by PhpStorm.
 * User: grest
 * Date: 10/14/14
 * Time: 11:40 AM
 */

class PaymentUpdateEvent extends PaymentEvent {

    /**
     * @var array
     */
    protected $changeBag;

    /**
     * @param Payment $payment
     * @param array   $changeBag
     */
    public function __construct(Payment $payment, $changeBag)
    {
        parent::__construct($payment);

        $this->changeBag = $changeBag;
    }

    /**
     * @return array
     */
    public function getChangeBag()
    {
        return $this->changeBag;
    }

    /**
     * @param array $changeBag
     *
     * @return $this
     */
    public function setChangeBag($changeBag)
    {
        $this->changeBag = $changeBag;

        return $this;
    }

}