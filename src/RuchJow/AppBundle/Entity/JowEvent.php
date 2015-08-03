<?php

namespace RuchJow\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RuchJow\TerritorialUnitsBundle\Entity\Commune;

/**
 * @ORM\Entity(repositoryClass="RuchJow\AppBundle\Entity\JowEventRepository")
 * @ORM\Table(name="jow_event")
 */
class JowEvent
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=false)
     *
     */
    protected $date;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $venue;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $link;

    /**
     * @var Commune
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\TerritorialUnitsBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id", nullable=false)
     */
    protected $commune;



    //*********************************
    //********** CONSTRUCTOR **********
    //*********************************

    public function __construct()
    {
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
    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * @param string $venue
     *
     * @return $this
     */
    public function setVenue($venue)
    {
        $this->venue = $venue;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     *
     * @return $this
     */
    public function setLink($link)
    {
        if (!preg_match('/^https?:\/\//', $link)) {
             $link = 'http://' . $link;
        }
        $this->link = $link;

        return $this;
    }

    /**
     * @return Commune
     */
    public function getCommune()
    {
        return $this->commune;
    }

    /**
     * @param Commune $commune
     *
     * @return $this
     */
    public function setCommune($commune)
    {
        $this->commune = $commune;

        return $this;
    }
}
