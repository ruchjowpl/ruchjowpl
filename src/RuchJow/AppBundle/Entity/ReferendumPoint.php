<?php

namespace RuchJow\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RuchJow\TerritorialUnitsBundle\Entity\Commune;

/**
 * @ORM\Entity(repositoryClass="RuchJow\AppBundle\Entity\ReferendumPointRepository")
 * @ORM\Table(name="referendum_point")
 */
class ReferendumPoint
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
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $subtitle="";

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1000, nullable=false)
     */
    protected $description = "";

    /**
     * @var Commune
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\TerritorialUnitsBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id", nullable=true)
     */
    protected $commune;

    /**
     * @var float
     *
     * @ORM\Column(type="float", precision=9, scale=7, nullable=true)
     */
    protected $lat;

    /**
     * @var float
     *
     * @ORM\Column(type="float", precision=9, scale=7, nullable=true)
     */
    protected $lng;



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
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * @param string $subtitle
     *
     * @return $this
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

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

    /**
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param float $lat
     *
     * @return $this
     */
    public function setLat($lat)
    {
        $this->lat = $lat;

        return $this;
    }

    /**
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param float $lng
     *
     * @return $this
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }
}
