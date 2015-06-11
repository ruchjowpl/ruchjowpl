<?php

namespace RuchJow\MessagesBundle\Listener;

use Doctrine\ORM\EntityManager;
use RuchJow\MessagesBundle\Entity\Folder;
use RuchJow\MessagesBundle\Entity\Manager;
use RuchJow\MessagesBundle\Entity\Thread;
use RuchJow\MessagesBundle\Entity\ThreadsParent;
use RuchJow\MessagesBundle\Event\MessageChangeEvent;
use RuchJow\MessagesBundle\Event\ThreadChangeEvent;
use RuchJow\MessagesBundle\MessageEvents;
use RuchJow\MessagesBundle\ThreadEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class ThreadEventsSubscriber implements EventSubscriberInterface
{

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(Manager $manager, EntityManager $entityManager)
    {
        $this->manager       = $manager;
        $this->entityManager = $entityManager;
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
            ThreadEvents::THREAD_CHANGED => array('onThreadChanged'),
            ThreadEvents::THREAD_REMOVED => array('onThreadChanged'),
        );
    }

    /**
     * @param ThreadChangeEvent $threadEvent
     */
    public function onThreadChanged(ThreadChangeEvent $threadEvent)
    {
        $thread = $threadEvent->getThread();

        if ($threadEvent->hasChangedField('threadsParent')) {
            /** @var ThreadsParent $oldThreadsParent */
            if ($oldThreadsParent = $threadEvent->getOldValue('threadsParent')) {
                $this->manager->updateThreadsParent($oldThreadsParent, false);
            }

            if ($thread) {
                if ($threadsParent = $thread->getThreadsParent()) {
                    $this->manager->updateThreadsParent($threadsParent, false);
                }
            }
        };
    }
}