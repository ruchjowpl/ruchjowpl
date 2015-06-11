<?php

namespace RuchJow\UserBundle\Event;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use RuchJow\UserBundle\Entity\User;
use Symfony\Component\EventDispatcher\Event;

class UserLifecycleEvent extends Event
{

    /**
     * @var User
     */
    protected $user;

    /**
     * @var LifecycleEventArgs
     */
    protected $doctrineEventArgs;

    public function __construct(User $user, LifecycleEventArgs $doctrineEventArgs = null)
    {
        $this->user = $user;
        $this->doctrineEventArgs = $doctrineEventArgs;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return LifecycleEventArgs
     */
    public function getDoctrineEventArgs()
    {
        return $this->doctrineEventArgs;
    }

}