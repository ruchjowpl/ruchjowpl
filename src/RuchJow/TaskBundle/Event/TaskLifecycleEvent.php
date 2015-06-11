<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 9/22/14
 * Time: 4:44 PM
 */

namespace RuchJow\TaskBundle\Event;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use RuchJow\TaskBundle\Entity\Task;
use Symfony\Component\EventDispatcher\Event;

class TaskLifecycleEvent extends Event
{

    /**
     * @var Task
     */
    protected $task;

    /**
     * @var LifecycleEventArgs
     */
    protected $doctrineEventArgs;

    public function __construct(Task $task, LifecycleEventArgs $doctrineEventArgs = null)
    {
        $this->task = $task;
        $this->doctrineEventArgs = $doctrineEventArgs;
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return LifecycleEventArgs
     */
    public function getDoctrineEventArgs()
    {
        return $this->doctrineEventArgs;
    }

}