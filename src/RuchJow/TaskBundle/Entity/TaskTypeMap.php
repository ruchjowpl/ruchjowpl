<?php

namespace RuchJow\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RuchJow\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="RuchJow\TaskBundle\Entity\TaskTypeMapRepository")
 * @ORM\Table(name="task_type_map")
 */
class TaskTypeMap
{
    const TYPE_ALL = '*';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $type;

    /**
     * @var boolean
     * @ORM\ManyToOne(targetEntity="RuchJow\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", nullable=false)
     */
    protected $user;

    //*********************************
    //********** CONSTRUCTOR **********
    //*********************************

    public function __construct()
    {
        // your own logic
    }

    //*********************************
    //****** GETTERS and SETTERS ******
    //*********************************


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return TaskTypeMap
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set user
     *
     * @param \RuchJow\UserBundle\Entity\User $user
     * @return TaskTypeMap
     */
    public function setUser(\RuchJow\UserBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \RuchJow\UserBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
