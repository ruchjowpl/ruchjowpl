<?php

namespace RuchJow\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="RuchJow\UserBundle\Entity\OrganisationRepository")
 * @ORM\Table(name="organisation")
 */
class Organisation
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
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="url", type="string", length=255, nullable=false, unique=true)
     */
    protected $url;

    /**
     * @var User[]
     *
     * @ORM\OneToMany(targetEntity="User", mappedBy="organisation")
     */
    protected $users;

    /**
     * @var boolean
     *
     * @ORM\Column(name="https", type="boolean", nullable=false)
     */
    protected $https = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="www_prefix", type="boolean", nullable=false)
     */
    protected $wwwPrefix = false;



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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Organisation
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
     * Set url
     *
     * @param string $url
     * @return Organisation
     */
    public function setUrl($url)
    {
        if (preg_match('/^(https:\/\/)/', $url)) {
            $this->setHttps(true);
        }

        $url = preg_replace('/^(https?:\/\/)?/', '', $url);

        if (preg_match('/^www\./', $url)) {
            $this->setWwwPrefix(true);
        }
        $url = preg_replace('/^www\./', '', $url);

        $this->url = $url;

        return $this;
    }

    /**
     * Get url
     *
     * @param bool $includeProtocol
     *
     * @return string
     */
    public function getUrl($includeProtocol = false)
    {
        return ($includeProtocol ? ($this->isHttps() ? 'https://' : 'http://') : '') .
            ($this->isWwwPrefix() ? 'www.' : '') .
            $this->url;
    }

    /**
     * Add users
     *
     * @param User $users
     *
*@return Organisation
     */
    public function addUser(User $users)
    {
        $this->users[] = $users;

        return $this;
    }

    /**
     * Remove users
     *
     * @param User $users
     */
    public function removeUser(User $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * @return boolean
     */
    public function isHttps()
    {
        return $this->https;
    }

    /**
     * @param boolean $https
     *
     * @return $this
     */
    public function setHttps($https)
    {
        $this->https = $https;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isWwwPrefix()
    {
        return $this->wwwPrefix;
    }

    /**
     * @param boolean $wwwPrefix
     *
     * @return $this
     */
    public function setWwwPrefix($wwwPrefix)
    {
        $this->wwwPrefix = $wwwPrefix;

        return $this;
    }



    public function toArray()
    {
        return array(
            'name' => $this->getName(),
            'url'  => ($this->isHttps() ? 'https://' : '') . $this->getUrl(),
        );
    }
}
