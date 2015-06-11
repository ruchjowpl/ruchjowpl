<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 9/23/14
 * Time: 1:25 PM
 */

namespace RuchJow\TaskBundle\Listener;


use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use RuchJow\TaskBundle\Event\TaskChangeEvent;
use RuchJow\TaskBundle\Event\TaskLifecycleEvent;
use RuchJow\TaskBundle\Services\TaskManager;
use RuchJow\TaskBundle\TaskEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TaskEventSubscriber implements EventSubscriberInterface
{

    /**
     * @var TaskManager
     */
    protected $taskManager;

    public function __construct(TaskManager $taskManager){
        $this->taskManager = $taskManager;
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
            TaskEvents::TASK_CHANGE      => array('onTaskChange'),
            TaskEvents::TASK_PRE_PERSIST => array('updateTask'),
            TaskEvents::TASK_PRE_UPDATE  => array('updateTask'),
        );
    }

    /**
     * @param TaskChangeEvent $taskEvent
     */
    public function onTaskChange(TaskChangeEvent $taskEvent)
    {
        $this->taskManager->sendTaskInfo(
            $taskEvent->getTask(),
            $taskEvent->isJustCreated(),
            $taskEvent->isJustCanceled()
        );
    }

    /**
     * @param TaskLifecycleEvent $event
     */
    public function updateTask(TaskLifecycleEvent $event)
    {
        $this->taskManager->updateTask($event->getTask(), $event->getDoctrineEventArgs());
    }
}