<?php

namespace RuchJow\PointsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RuchJow\TerritorialUnitsBundle\Entity\Commune;
use RuchJow\TerritorialUnitsBundle\Entity\Country;
use RuchJow\UserBundle\Entity\Organisation;
use RuchJow\UserBundle\Entity\User;

/**
 * @ORM\Entity(repositoryClass="RuchJow\PointsBundle\Entity\PointsEntryRepository")
 * @ORM\Table(name="points_entry")
 */
class PointsEntry
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
     * @var Organisation
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\UserBundle\Entity\Organisation")
     * @ORM\JoinColumn(name="organisation_id", referencedColumnName="id", nullable=true)
     */
    protected $organisation;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\TerritorialUnitsBundle\Entity\Country")
     * @ORM\JoinColumn(name="country_code", referencedColumnName="code", nullable=true)
     */
    protected $country;

    /**
     * @var Commune
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\TerritorialUnitsBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id", referencedColumnName="id", nullable=true)
     */
    protected $commune;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=false)
     */
    protected $points;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $dataSerial;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     */
    protected $date;

    //*********************************
    //********** CONSTRUCTOR **********
    //*********************************

    public function __construct()
    {
        // your own logic
        $this->date = new \DateTime();
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
     * Set points
     *
     * @param integer $points
     * @return PointsEntry
     */
    public function setPoints($points)
    {
        $this->points = intval($points);

        return $this;
    }

    /**
     * Get points
     *
     * @return integer
     */
    public function getPoints()
    {
        return $this->points;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return PointsEntry
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
     * Set dataSerial
     *
     * @param string $dataSerial
     * @return PointsEntry
     */
    public function setDataSerial($dataSerial)
    {
        $this->dataSerial = $dataSerial;
        $this->data = unserialize($dataSerial);

        return $this;
    }

    /**
     * Get dataSerial
     *
     * @return string
     */
    public function getDataSerial()
    {
        return $this->dataSerial;
    }

    /**
     * Sets data
     *
     * @param mixed $data
     *
     * @return PointsEntry
     */
    public function setData($data)
    {
        $this->data = $data;
        $this->dataSerial = serialize($data);

        return $this;
    }

    /**
     * Gets data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return PointsEntry
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set user
     *
     * @param User $user
     *
     * @return PointsEntry
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set organisation
     *
     * @param Organisation $organisation
     *
     * @return PointsEntry
     */
    public function setOrganisation(Organisation $organisation = null)
    {
        $this->organisation = $organisation;

        return $this;
    }

    /**
     * Get organisation
     *
     * @return Organisation
     */
    public function getOrganisation()
    {
        return $this->organisation;
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param Country $country
     *
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Set commune
     *
     * @param Commune $commune
     *
     * @return PointsEntry
     */
    public function setCommune(Commune $commune = null)
    {
        $this->commune = $commune;

        return $this;
    }

    /**
     * Get commune
     *
     * @return Commune
     */
    public function getCommune()
    {
        return $this->commune;
    }
}
