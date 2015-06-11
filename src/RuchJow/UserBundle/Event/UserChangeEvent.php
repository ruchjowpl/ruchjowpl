<?php

namespace RuchJow\UserBundle\Event;

use RuchJow\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class UserChangeEvent extends Event{

    /**
     * @var User
     */
    protected $user;

    /**
     * @var array
     */
    protected $changeSet;

    /**
     * @param User  $user
     * @param array $changeSet
     */
    public function __construct(User $user, $changeSet){
        $this->user = $user;
        $this->changeSet = $changeSet;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
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