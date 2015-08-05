<?php

namespace RuchJow\StatisticsBundle\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use RuchJow\TransferujPlBundle\Event\PaymentEvent;
use Ruchjow\TransferujPlBundle\TransferujPlEvents;
use RuchJow\PointsBundle\Event\PointsEvent;
use RuchJow\PointsBundle\PointsEvents;
use RuchJow\StatisticsBundle\Entity\StatisticManager;
use RuchJow\StatisticsBundle\RuchJowStatisticsBundle;
use RuchJow\UserBundle\Event\UserChangeEvent;
use RuchJow\UserBundle\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EventsSubscriber implements EventSubscriberInterface
{

    /**
     * @var StatisticManager
     */
    protected $statisticManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(StatisticManager $statsManager, Registry $doctrine)
    {
        $this->statisticManager = $statsManager;
        $this->entityManager    = $doctrine->getManager();
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            UserEvents::USER_POST_PERSIST         => array('onUserChanged'),
            UserEvents::USER_POST_UPDATE          => array('onUserChanged'),

            TransferujPlEvents::PAYMENT_CONFIRMED => array('onDonation'),
            PointsEvents::POINTS_ADD_EVENT        => array('onPointsAdd'),
        );
    }

    /**
     * @param UserChangeEvent $userEvent
     */
    public function onUserChanged(UserChangeEvent $userEvent)
    {
        $user = $userEvent->getUser();

        $oldSupports = $userEvent->hasChangedField('supports') ? $userEvent->getOldValue('supports') : $user->isSupports();
        $oldLocalGov = $userEvent->hasChangedField('localGov') ? $userEvent->getOldValue('localGov') : $user->isLocalGov();
        $newSupports = $user->isSupports();
        $newLocalGov = $user->isLocalGov();

        $flush = false;

        if (!$oldSupports && $newSupports) {
            // User started to support
            $this->statisticManager->incStatistic(RuchJowStatisticsBundle::STAT_SUPPORTING_USERS);
            $flush = true;
        } elseif ($oldSupports && !$newSupports) {
            // User is no longer supporting
            $this->statisticManager->decStatistic(RuchJowStatisticsBundle::STAT_SUPPORTING_USERS);
            $flush = true;
        }


        if (
            (!$oldSupports || !$oldLocalGov)
            && $newSupports && $newLocalGov
        ) {
            // User is now supporting as localGov
            $this->statisticManager->incStatistic(RuchJowStatisticsBundle::STAT_SUPPORTING_USERS_LOCAL_GOV);
            $flush = true;
        } elseif (
            $oldSupports && $oldLocalGov
            && (!$newSupports || !$newLocalGov)
        ) {
            // User is no longer supporting as localGov
            $this->statisticManager->decStatistic(RuchJowStatisticsBundle::STAT_SUPPORTING_USERS_LOCAL_GOV);
            $flush = true;
        }

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function onDonation(PaymentEvent $event)
    {
        $payment = $event->getPayment();
        $data    = json_decode($payment->getCrc(), true);

        if ($data && isset($data['type']) && $data['type'] === 'donation') {
            $cnt   = $this->statisticManager->incStatistic(RuchJowStatisticsBundle::STAT_DONATIONS_COUNT);
            $total = $this->statisticManager->incStatistic(RuchJowStatisticsBundle::STAT_DONATIONS, $payment->getPaid());

            $this->statisticManager->setStatisticValue(RuchJowStatisticsBundle::STAT_DONATIONS_AVG, $cnt ? round($total / $cnt, 2) : .0);

            // Flush is not needed as PAYMENT_CONFIRMED event is triggered just before flush.
            // $this->entityManager->flush();
        }
    }

    public function onPointsAdd(PointsEvent $event)
    {
        $pointsEntry = $event->getPointsEntry();
        $points      = $pointsEntry->getPoints();

        $this->statisticManager->incStatistic(
            RuchJowStatisticsBundle::STAT_POINTS_TOTAL,
            $points
        );

        $this->statisticManager->incStatistic(
            array(
                RuchJowStatisticsBundle::STAT_POINTS_USER,
                $pointsEntry->getUser()->getId()
            ),
            $points
        );

        if ($country = $pointsEntry->getCountry()) {
            $this->statisticManager->incStatistic(
                array(
                    RuchJowStatisticsBundle::STAT_POINTS_COUNTRY,
                    $country->getCode()
                ),
                $points
            );
        }

        if ($commune = $pointsEntry->getCommune()) {
            $this->statisticManager->incStatistic(
                array(
                    RuchJowStatisticsBundle::STAT_POINTS_COMMUNE,
                    $commune->getId()
                ),
                $points
            );

            $this->statisticManager->incStatistic(
                array(
                    RuchJowStatisticsBundle::STAT_POINTS_DISTRICT,
                    $commune
                        ->getDistrict()
                        ->getId()
                ),
                $points
            );

            $this->statisticManager->incStatistic(
                array(
                    RuchJowStatisticsBundle::STAT_POINTS_REGION,
                    $commune
                        ->getDistrict()
                        ->getRegion()
                        ->getId()
                ),
                $points
            );
        }

        if ($org = $pointsEntry->getOrganisation()) {
            $this->statisticManager->incStatistic(
                array(
                    RuchJowStatisticsBundle::STAT_POINTS_ORGANISATION,
                    $org->getId()
                ),
                $points
            );
        }

    }
}