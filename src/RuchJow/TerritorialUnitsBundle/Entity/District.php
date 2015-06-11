<?php

namespace RuchJow\TerritorialUnitsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="RuchJow\TerritorialUnitsBundle\Entity\DistrictRepository")
 * @ORM\Table(name="district")
 */
class District
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
     * @var Region
     *
     * @ORM\ManyToOne(targetEntity="Region", inversedBy="districts")
     * @ORM\JoinColumn(name="region_id")
     */
    protected $region;

    /**
     * @var ArrayCollection|Commune[]
     *
     * @ORM\OneToMany(targetEntity="Commune", mappedBy="district")
     */
    protected $communes;


    //*********************************
    //********** CONSTRUCTOR **********
    //*********************************

    public function __construct()
    {
        $this->communes = new ArrayCollection();
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
     * @return District
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
     * Add communes
     *
     * @param Commune $communes
     *
*@return District
     */
    public function addCommune(Commune $communes)
    {
        $this->communes[] = $communes;

        return $this;
    }

    /**
     * Remove communes
     *
     * @param Commune $communes
     */
    public function removeCommune(Commune $communes)
    {
        $this->communes->removeElement($communes);
    }

    /**
     * Get communes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCommunes()
    {
        return $this->communes;
    }

    /**
     * Set region
     *
     * @param Region $region
     *
*@return District
     */
    public function setRegion(Region $region = null)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * Get region
     *
     * @return Region
     */
    public function getRegion()
    {
        return $this->region;
    }

    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'name' => $this->getName(),
            'region' => $this->getRegion()->toArray(),
        );
    }
}
