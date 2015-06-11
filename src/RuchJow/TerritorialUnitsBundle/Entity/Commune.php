<?php

namespace RuchJow\TerritorialUnitsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="RuchJow\TerritorialUnitsBundle\Entity\CommuneRepository")
 * @ORM\Table(name="commune")
 */
class Commune
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
     * @ORM\Column(type="string", nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $type;

    /**
     * @var int
     *
     * @ORM\Column(name="teryt_id", type="integer")
     */
    protected $terytId;

    /**
     * @var District
     *
     * @ORM\ManyToOne(targetEntity="District", inversedBy="communes")
     * @ORM\JoinColumn(name="district_id")
     */
    protected $district;

    /**
     * @var ArrayCollection|PostCode[]
     *
     * @ORM\ManyToMany(targetEntity="PostCode", mappedBy="communes")
     */
    protected $postCodes;


    //*********************************
    //********** CONSTRUCTOR **********
    //*********************************

    public function __construct()
    {
        $this->postCodes = new ArrayCollection();
    }

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
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return Commune
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Set TERYT database id
     *
     * @return int
     */
    function getTerytId()
    {
        return $this->terytId;
    }

    /**
     * Get TERYT database id
     *
     * @return int
     */
    function setTerytId($terytId)
    {
        $this->terytId = (int)$terytId;

        return $this;
    }

    /**
     * Set district
     *
     * @param District $district
     *
     * @return Commune
     */
    public function setDistrict(District $district = null)
    {
        $this->district = $district;

        return $this;
    }

    /**
     * Get district
     *
     * @return District
     */
    public function getDistrict()
    {
        return $this->district;
    }

    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'type' => $this->getType(),
            'district' => $this->getDistrict()->toArray(),
        );
    }
}
