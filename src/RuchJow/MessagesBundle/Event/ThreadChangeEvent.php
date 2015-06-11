<?php

namespace RuchJow\MessagesBundle\Event;

use RuchJow\MessagesBundle\Entity\Thread;
use Symfony\Component\EventDispatcher\Event;

class ThreadChangeEvent extends Event {

    /**
     * @var Thread
     */
    protected $thread;

    /**
     * @var array
     */
    protected $changeSet;

    /**
     * @param Thread $thread
     * @param array   $changeSet
     */
    public function __construct(Thread $thread, $changeSet)
    {
        $this->thread = $thread;
        $this->changeSet = $changeSet;
    }

    /**
     * @return Thread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @return array
     */
    public function getChangeSet()
    {
        return $this->changeSet;
    }

    /**
     * @param string $fieldName
     *
     * @return mixed
     */
    public function getOldValue($fieldName)
    {
        if (!array_key_exists($fieldName, $this->changeSet)) {
            throw new \InvalidArgumentException('Field ' . $fieldName . ' not included in changeSet.');
        }

        return $this->changeSet[$fieldName];
    }

    public function hasChangedField($fieldName)
    {
        return array_key_exists($fieldName, $this->changeSet);
    }
}