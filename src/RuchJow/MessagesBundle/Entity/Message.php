<?php

namespace RuchJow\MessagesBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use RuchJow\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="RuchJow\MessagesBundle\Entity\MessageRepository")
 * @ORM\Table(name="msg_message")
 */
class Message
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
     * @var Folder
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\MessagesBundle\Entity\Folder", inversedBy="messages")
     * @ORM\JoinColumn(name="folder_id", referencedColumnName="id", nullable=false)
     */
    protected $folder;

    /**
     * @var Thread
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\MessagesBundle\Entity\Thread", inversedBy="messages")
     * @JoinColumn(name="thread_id", referencedColumnName="id", nullable=false)
     */
    protected $thread;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\UserBundle\Entity\User")
     * @JoinColumn(name="sender_id", referencedColumnName="id", nullable=false)
     */
    protected $sender;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\UserBundle\Entity\User")
     * @JoinColumn(name="recipient_id", referencedColumnName="id", nullable=false)
     */
    protected $recipient;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text", length=1000, nullable=false)
     */
    protected $body;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255, nullable=true)
     */
    protected $subject;

    /**
     * @var boolean
     *
     * @ORM\Column(name="is_read", type="boolean", nullable=false)
     */
    protected $isRead = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="sent_at", type="datetime", nullable=false)
     */
    protected $sentAt;

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
        $this->sentAt = new \DateTime();
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
     * @return Folder
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * @param mixed $folder
     *
     * @return $this
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * @return Thread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * @param Thread $thread
     *
     * @return $this
     */
    public function setThread($thread)
    {
        $this->thread = $thread;

        return $this;
    }


    /**
     * @return User
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param User $sender
     *
     * @return $this
     */
    public function setSender($sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * @return User
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param User $recipient
     *
     * @return $this
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param string $body
     *
     * @return $this
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    /**
     * @param boolean $isRead
     *
     * @return $this
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;

        if ($isRead && !$this->getReadAt()) {
            $this->setReadAt(new \DateTime());
        }

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getSentAt()
    {
        return $this->sentAt;
    }

    /**
     * @param \DateTime $sentAt
     *
     * @return $this
     */
    public function setSentAt($sentAt)
    {
        $this->sentAt = $sentAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getReadAt()
    {
        return $this->readAt;
    }

    /**
     * @param \DateTime $readAt
     *
     * @return $this
     */
    public function setReadAt($readAt)
    {
        $this->readAt = $readAt;

        return $this;
    }
}
