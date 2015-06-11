<?php

namespace RuchJow\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RuchJow\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="RuchJow\TaskBundle\Entity\TaskStatusRepository")
 * @ORM\Table(name="task_status")
 */
class TaskStatus
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var boolean
     * @ORM\Column(name="is_new", type="boolean", nullable=false)
     */
    protected $new;

    /**
     * @var boolean
     * @ORM\Column(name="is_closed", type="boolean", nullable=false)
     */
    protected $closed;

    /**
     * @var boolean
     * @ORM\Column(name="is_canceled", type="boolean", nullable=false)
     */
    protected $canceled;

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
     * Set id
     *
     * @param string $id
     * @return TaskStatus
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return TaskStatus
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set new
     *
     * @param boolean $new
     * @return TaskStatus
     */
    public function setNew($new)
    {
        $this->new = $new;

        return $this;
    }

    /**
     * Get new
     *
     * @return boolean
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Set closed
     *
     * @param boolean $closed
     * @return TaskStatus
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;

        return $this;
    }

    /**
     * Get closed
     *
     * @return boolean
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * Set canceled
     *
     * @param boolean $canceled
     * @return TaskStatus
     */
    public function setCanceled($canceled)
    {
        $this->canceled = $canceled;

        return $this;
    }

    /**
     * Get canceled
     *
     * @return boolean
     */
    public function isCanceled()
    {
        return $this->canceled;
    }


    /**
     * Get new
     *
     * @return boolean
     */
    public function getNew()
    {
        return $this->new;
    }

    /**
     * Get closed
     *
     * @return boolean
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * Get canceled
     *
     * @return boolean
     */
    public function getCanceled()
    {
        return $this->canceled;
    }
}
