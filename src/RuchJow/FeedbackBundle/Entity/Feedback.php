<?php

namespace RuchJow\FeedbackBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RuchJow\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="RuchJow\FeedbackBundle\Entity\FeedbackRepository")
 * @ORM\Table(name="feedback")
 */
class Feedback
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
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id", nullable=true)
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true, unique=false)
     */
    protected $nick;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false, unique=false)
     */
    protected $date;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false, unique=false)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1000, nullable=false, unique=false)
     */
    protected $description;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false, unique=false)
     */
    protected $contact = '';


    /**
     * @var string
     *
     * @ORM\Column(type="string", length=39, nullable=true, unique=false)
     */
    protected $ip;



    //*********************************
    //****** GETTERS and SETTERS ******
    //*********************************

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     * @param string $nick
     *
     * @return $this
     */
    public function setNick($nick)
    {
        $this->nick = $nick;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     *
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getIp()
    {
        return $this->ip;
    }

    /**
     * @param string $ip
     *
     * @return $this
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @param string $contact
     *
     * @return $this
     */
    public function setContact($contact)
    {
        $this->contact = $contact;

        return $this;
    }


}
