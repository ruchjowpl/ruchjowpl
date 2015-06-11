<?php

namespace RuchJow\TaskBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RuchJow\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="RuchJow\TaskBundle\Entity\TaskRepository")
 * @ORM\Table(name="task")
 */
class Task
{
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
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $assignedTo;


    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=false)
     */
    protected $content;

    /**
     * @var int
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\TaskBundle\Entity\TaskStatus")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id", nullable=false)
     */
    protected $status;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="canceled_at", type="datetime", nullable=true)
     */
    protected $canceledAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="closed_at", type="datetime", nullable=true)
     */
    protected $closedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="read_at", type="datetime", nullable=true)
     */
    protected $readAt;

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
     * @return Task
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
     * Set title
     *
     * @param string $title
     * @return Task
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Task
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Task
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set canceledAt
     *
     * @param \DateTime $canceledAt
     * @return Task
     */
    public function setCanceledAt($canceledAt)
    {
        $this->canceledAt = $canceledAt;

        return $this;
    }

    /**
     * Get canceledAt
     *
     * @return \DateTime
     */
    public function getCanceledAt()
    {
        return $this->canceledAt;
    }

    /**
     * Sets closedAt
     *
     * @param \DateTime $closedAt
     *
     * @return Task
     */
    public function setClosedAt($closedAt)
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    /**
     * Gets closedAt
     *
     * @return \DateTime
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }


    /**
     * Set readAt
     *
     * @param \DateTime $readAt
     * @return Task
     */
    public function setReadAt($readAt)
    {
        $this->readAt = $readAt;

        return $this;
    }

    /**
     * Get readAt
     *
     * @return \DateTime
     */
    public function getReadAt()
    {
        return $this->readAt;
    }

    /**
     * Set assignedTo
     *
     * @param User $assignedTo
     *
     * @return Task
     */
    public function setAssignedTo(User $assignedTo = null)
    {
        $this->assignedTo = $assignedTo;

        return $this;
    }

    /**
     * Get assignedTo
     *
     * @return User
     */
    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    /**
     * Set status
     *
     * @param TaskStatus $status
     *
     * @return Task
     */
    public function setStatus(TaskStatus $status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return TaskStatus
     */
    public function getStatus()
    {
        return $this->status;
    }
}
