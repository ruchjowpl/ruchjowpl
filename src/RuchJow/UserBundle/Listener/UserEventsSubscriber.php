<?php

namespace RuchJow\UserBundle\Listener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use RuchJow\UserBundle\Entity\User;
use RuchJow\UserBundle\Entity\UserManager;
use RuchJow\UserBundle\Event\UserLifecycleEvent;
use RuchJow\UserBundle\UserEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class UserEventsSubscriber implements EventSubscriberInterface
{

    /**
     * @var UserManager
     */
    protected $userManager;

    public function __construct(UserManager $userManager){
        $this->userManager = $userManager;
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
            UserEvents::USER_PRE_PERSIST => array('onUserAboutToChange'),
            UserEvents::USER_PRE_UPDATE  => array('onUserAboutToChange'),
        );
    }

    /**
     * @param UserLifecycleEvent $userEvent
     */
    public function onUserAboutToChange(UserLifecycleEvent $userEvent)
    {
        /** @var User $user */
        $user = $userEvent->getUser();

        $this->userManager->updateUserFields($user);
    }
}