<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 9/22/14
 * Time: 4:44 PM
 */

namespace RuchJow\TaskBundle\Event;

use RuchJow\TaskBundle\Entity\Task;
use Symfony\Component\EventDispatcher\Event;

class TaskChangeEvent extends Event{

    /**
     * @var Task
     */
    protected $task;

    protected $justCreated;

    protected $justCanceled;

    public function __construct(Task $task){
        $this->task = $task;
    }

    /**
     * @return Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * @return boolean
     */
    public function isJustCanceled()
    {
        return $this->justCanceled;
    }

    /**
     * @param boolean $isJustCanceled
     *
     * @return $this
     */
    public function setJustCanceled($isJustCanceled)
    {
        $this->justCanceled = $isJustCanceled;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isJustCreated()
    {
        return $this->justCreated;
    }

    /**
     * @param boolean $isJustCreated
     *
     * @return $this
     */
    public function setJustCreated($isJustCreated)
    {
        $this->justCreated = $isJustCreated;

        return $this;
    }



}