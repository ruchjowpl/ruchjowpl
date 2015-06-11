<?php

namespace RuchJow\PointsBundle\Event;

use RuchJow\PointsBundle\Entity\PointsEntry;
use Symfony\Component\EventDispatcher\Event;


class PointsEvent extends Event
{

    /**
     * @var PointsEntry
     */
    protected $pointsEntry;

    /**
     * @param PointsEntry $pointsEntry
     */
    public function __construct(PointsEntry $pointsEntry)
    {
        $this->pointsEntry = $pointsEntry;
    }

    /**
     * @return PointsEntry
     */
    public function getPointsEntry()
    {
        return $this->pointsEntry;
    }

    /**
     * @param PointsEntry $pointsEntry
     *
     * @return $this
     */
    public function setPayment($pointsEntry)
    {
        $this->pointsEntry = $pointsEntry;

        return $this;
    }
}