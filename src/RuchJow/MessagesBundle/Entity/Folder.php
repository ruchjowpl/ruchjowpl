<?php

namespace RuchJow\MessagesBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use RuchJow\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="RuchJow\MessagesBundle\Entity\FolderRepository")
 * @ORM\Table(name="msg_folder")
 */
class Folder
{
    const FOLDER_INBOX = '#inbox';
    const FOLDER_SENT  = '#sent';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\UserBundle\Entity\User")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $owner;

    /**
     * @var integer
     *
     * @ORM\Column(name="message_cnt", type="integer", nullable=false)
     */
    protected $messageCnt = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="unread_cnt", type="integer", nullable=false)
     */
    protected $unreadCnt = 0;

    /**
     * @var Message[]
     *
     * @ORM\OneToMany(targetEntity="RuchJow\MessagesBundle\Entity\Message", mappedBy="folder")
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
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
     * @return int
     */
    public function getUnreadCnt()
    {
        return $this->unreadCnt;
    }

    /**
     * @param int $unreadCnt
     *
     * @return $this
     */
    public function setUnreadCnt($unreadCnt)
    {
        $this->unreadCnt = $unreadCnt;

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
