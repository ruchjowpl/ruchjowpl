<?php

namespace RuchJow\MessagesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use RuchJow\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="RuchJow\MessagesBundle\Entity\ThreadRepository")
 * @ORM\Table(name="msg_thread")
 */
class Thread
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;


    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\UserBundle\Entity\User")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $owner;

    /**
     * @var ThreadsParent
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\MessagesBundle\Entity\ThreadsParent")
     * @JoinColumn(name="threads_parent_id", referencedColumnName="id", nullable=false)
     */
    protected $threadsParent;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_message_at", type="datetime", nullable=true)
     */
    protected $lastMessageAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="message_cnt", type="integer", nullable=false)
     */
    protected $messageCnt = 0;

    /**
     * @var Message[]
     *
     * @ORM\OneToMany(targetEntity="RuchJow\MessagesBundle\Entity\Message", mappedBy="thread")
     */
    protected $messages;

    //*********************************
    //********** CONSTRUCTOR **********
    //*********************************

    public function __construct()
    {
        $this->messages = new ArrayCollection();
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
     * @return User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     *
     * @return $this
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * @return ThreadsParent
     */
    public function getThreadsParent()
    {
        return $this->threadsParent;
    }

    /**
     * @param ThreadsParent $threadsParent
     *
     * @return $this
     */
    public function setThreadsParent($threadsParent)
    {
        $this->threadsParent = $threadsParent;

        return $this;
    }



    /**
     * @return \DateTime
     */
    public function getLastMessageAt()
    {
        return $this->lastMessageAt;
    }

    /**
     * @param \DateTime $lastMessageAt
     *
     * @return $this
     */
    public function setLastMessageAt($lastMessageAt)
    {
        $this->lastMessageAt = $lastMessageAt;

        return $this;
    }

    /**
     * @return int
     */
    public function getMessageCnt()
    {
        return $this->messageCnt;
    }

    /**
     * @param int $messageCnt
     *
     * @return $this
     */
    public function setMessageCnt($messageCnt)
    {
        $this->messageCnt = $messageCnt;

        return $this;
    }

    /**
     * @return Message[]
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param Message[] $messages
     *
     * @return $this
     */
    public function setMessages($messages)
    {
        $this->messages = $messages;

        return $this;
    }
}
