<?php

namespace RuchJow\StatisticsBundle\Controller;

use Ruchjow\TransferujPlBundle\Service\PaymentManager;
use RuchJow\PageFoundationBundle\Controller\ModelController as PageFoundationModelController;
use RuchJow\PointsBundle\Services\PointsManager;
use RuchJow\StatisticsBundle\Entity\StatisticManager;

/**
 * Class ModelController - provides basic helper functions.
 *
 * @package RuchJow\StatisticsBundle\Controller
 */
class ModelController extends PageFoundationModelController
{
    /**
     * @return PointsManager
     */
    public function getPointsManager()
    {
        return $this->get('ruch_jow_points.points_manager');
    }

    /**
     * @return StatisticManager
     */
    public function getStatisticManager()
    {
        return $this->get('ruch_jow_statistics.statistic_manager');
    }

    /**
     * @return PaymentManager
     */
    public function getPaymentManager()
    {
        return $this->get('ruch_jow_transferuj_pl.payment_manager');
    }

}