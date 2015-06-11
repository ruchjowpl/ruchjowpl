<?php

namespace RuchJow\MessagesBundle\Event;

use RuchJow\MessagesBundle\Entity\Message;
use Symfony\Component\EventDispatcher\Event;

class MessageChangeEvent extends Event {

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var array
     */
    protected $changeSet;

    /**
     * @param Message $message
     * @param array   $changeSet
     */
    public function __construct(Message $message, $changeSet)
    {
        $this->message = $message;
        $this->changeSet = $changeSet;
    }

    /**
     * @return Message
     */
    public function getMessage()
    {
        return $this->message;
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