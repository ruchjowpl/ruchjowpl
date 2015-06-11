<?php

namespace RuchJow\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="RuchJow\UserBundle\Entity\PreSignedUserDataRepository")
 * @ORM\Table(name="pre_signed_user_data")
 */
class PreSignedUserData
{
    /**
     * @ORM\Id
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=false)
     */
    protected $token;

    /**
     * @var string
     *
     * @ORM\Column(name="nick", type="string", length=255, nullable=false)
     */
    protected $nick;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false)
     */
    protected $email;

    //*********************************
    //********** CONSTRUCTOR **********
    //*********************************

    public function __construct()
    {
        // your own logic
        $this->users = new ArrayCollection();
    }

    //*********************************
    //****** GETTERS and SETTERS ******
    //*********************************

    /**
     * Set nick
     *
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
     * Get nick
     *
     * @return string
     */
    public function getNick()
    {
        return $this->nick;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return $this
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return PreSignedUserData
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get token
     *
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }
}
