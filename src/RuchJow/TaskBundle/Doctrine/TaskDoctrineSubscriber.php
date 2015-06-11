<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 9/23/14
 * Time: 9:26 AM
 */

namespace RuchJow\TaskBundle\Doctrine;


use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use RuchJow\TaskBundle\Entity\Task;
use RuchJow\TaskBundle\Entity\TaskStatus;
use RuchJow\TaskBundle\Event\TaskChangeEvent;
use RuchJow\TaskBundle\Event\TaskLifecycleEvent;
use RuchJow\TaskBundle\TaskEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class TaskDoctrineSubscriber implements EventSubscriber
{

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $tasksData = array();

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
            'postPersist',
            'postFlush',
        );
    }


    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Task) {

            // Dispatch task pre update event.
            $this->dispatcher->dispatch(
                TaskEvents::TASK_PRE_PERSIST,
                new TaskLifecycleEvent($entity, $args)
            );
        }
    }


    /**
     * @param LifecycleEventArgs|PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Task) {
            $this->dispatcher->dispatch(
                TaskEvents::TASK_PRE_UPDATE,
                new TaskLifecycleEvent($entity, $args)
            );

            if ($args->hasChangedField('status')) {
                $this->tasksData[$entity->getId()] = array(
                    'old_status' => $args->getOldValue('status'),
                    'entity'     => $entity,
                );
            };
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Task) {
            $this->tasksData[$entity->getId()] = array(
                'old_status' => null,
                'entity'     => $entity,
            );
        }
    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->handleFlush($args);
    }

    /**
     * @param PostFlushEventArgs $args
     */
    protected function handleFlush(PostFlushEventArgs $args)
    {
        $tasksData = $this->tasksData;
        $this->tasksData = array();

        foreach ($tasksData as $taskData) {
            /** @var Task $task */
            $task = $taskData['entity'];
            /** @var TaskStatus $oldStatus */
            $oldStatus = $taskData['old_status'];

            $changes = array(
                'new'      => false,
                'canceled' => false,
            );


            // Check if task has got status new.
            if (
                $task->getStatus()->isNew()
                && (
                    !$oldStatus
                    || !$oldStatus->isNew()
                )
            ) {
                $changes['new'] = true;
            }

            // Check if task has been canceled.
            if (
                $task->getStatus()->isCanceled()
                && $oldStatus
                && $oldStatus->isCanceled()
            ) {
                $changes['canceled'] = true;
            }


            if (!empty($changes)) {
                $event = new TaskChangeEvent($task);

                if ($changes['new']) {
                    $event->setJustCreated(true);
                }

                if ($changes['canceled']) {
                    $event->setJustCreated(true);
                }

                $this->dispatcher->dispatch(TaskEvents::TASK_CHANGE, $event);
            }

        }
    }
}