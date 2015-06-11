<?php

namespace RuchJow\PointsBundle\Listener;

use RuchJow\TransferujPlBundle\Event\PaymentEvent;
use RuchJow\TransferujPlBundle\TransferujPlEvents;
use RuchJow\PointsBundle\Services\PointsManager;
use RuchJow\UserBundle\Entity\User;
use RuchJow\UserBundle\Entity\UserManager;
use RuchJow\UserBundle\Event\UserChangeEvent;
use RuchJow\UserBundle\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class VariousEventsSubscriber implements EventSubscriberInterface
{

    /**
     * @var UserManager
     */
    protected $userManager;

    /**
     * @var PointsManager
     */
    protected $pointsManager;

    public function __construct(PointsManager $pointsManager, UserManager $userManager)
    {
        $this->pointsManager = $pointsManager;
        $this->userManager   = $userManager;
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
            TransferujPlEvents::PAYMENT_CONFIRMED => array('onPaymentConfirmed'),
            UserEvents::USER_POST_PERSIST         => array('onUserChanged'),
            UserEvents::USER_POST_UPDATE          => array('onUserChanged'),
        );
    }

    /**
     * @param PaymentEvent $paymentEvent
     */
    public function onPaymentConfirmed(PaymentEvent $paymentEvent)
    {
        $payment = $paymentEvent->getPayment();
//        $crc = $payment->getCrc();

        $data = json_decode($payment->getCrc(), true);
        if (
            isset($data['type'])
            && $data['type'] === 'donation'
            && isset($data['user'])
            && is_int($data['user'])
        ) {
            /** @var User $user */
            $user = $this->userManager->findUserBy(array('id' => $data['user']));

            if (!$user) {
                return;
            }

            $points = $this->pointsManager->getPointsByType('make.donation.1pln');

            $this->pointsManager->addPoints(
                $user,
                'donation',
                (int) ($points * $payment->getPaid()),
                null,
                false
            );
        }
    }

    /**
     * @param UserChangeEvent $userEvent
     */
    public function onUserChanged(UserChangeEvent $userEvent)
    {
        $user = $userEvent->getUser();

        $oldSupports = $userEvent->hasChangedField('supports') ? $userEvent->getOldValue('supports') : $user->isSupports();
        $newSupports = $user->isSupports();

        $flush = false;

        if (!$oldSupports && $newSupports) {
            // User started to support

            $this->pointsManager->addPoints($user, 'user.support', null, null, false);

            if ($user->getReferrer()) {
                $additionalData = array(
                    'user' => $user->getId()
                );
                $this->pointsManager->addPoints($user->getReferrer(), 'user.referral', null, $additionalData, true);
            }
        }
    }



}