<?php

namespace RuchJow\TerritorialUnitsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="RuchJow\TerritorialUnitsBundle\Entity\RegionRepository")
 * @ORM\Table(name="region")
 */
class Region
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
     * @var ArrayCollection|District[]
     *
     * @ORM\OneToMany(targetEntity="District", mappedBy="region")
     */
    protected $districts;


    //*********************************
    //********** CONSTRUCTOR **********
    //*********************************

    public function __construct()
    {
        $this->districts = new ArrayCollection();
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
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Add districts
     *
     * @param District $districts
     *
     * @return Region
     */
    public function addDistrict(District $districts)
    {
        $this->districts[] = $districts;

        return $this;
    }

    /**
     * Remove districts
     *
     * @param District $districts
     */
    public function removeDistrict(District $districts)
    {
        $this->districts->removeElement($districts);
    }

    /**
     * Get districts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDistricts()
    {
        return $this->districts;
    }

    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
        );
    }
}
