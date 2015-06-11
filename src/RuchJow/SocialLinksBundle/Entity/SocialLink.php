<?php

namespace RuchJow\SocialLinksBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\JoinColumn;
use RuchJow\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="SocialLinkRepository")
 * @ORM\Table(name="social_link")
 */
class SocialLink
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false, unique=false)
     */
    protected $service;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false, unique=false)
     */
    protected $path;

    /**
     * Full path with prefix actual at time of save (taken from current configuration).
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false, unique=false)
     */
    protected $fullPath;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\UserBundle\Entity\User")
     * @JoinColumn(name="user_id", referencedColumnName="id", nullable=false)
     */
    protected $user;

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
     * Set service
     *
     * @param string $service
     * @return SocialLink
     */
    public function setService($service)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get service
     *
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return SocialLink
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set fullPath
     *
     * @param string $fullPath
     * @return SocialLink
     */
    public function setFullPath($fullPath)
    {
        $this->fullPath = $fullPath;

        return $this;
    }

    /**
     * Get fullPath
     *
     * @return string
     */
    public function getFullPath()
    {
        return $this->fullPath;
    }

    /**
     * Set user
     *
     * @param \RuchJow\UserBundle\Entity\User $user
     * @return SocialLink
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
