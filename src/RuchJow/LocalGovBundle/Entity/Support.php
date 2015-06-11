<?php

namespace RuchJow\LocalGovBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RuchJow\TerritorialUnitsBundle\Entity\Commune;

/**
 * @ORM\Entity(repositoryClass="RuchJow\LocalGovBundle\Entity\SupportRepository")
 * @ORM\Table(name="lgov_support")
 */
class Support
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
     * @var string
     *
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    protected $link;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=1000, nullable=true)
     */
    protected $linkTitle;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $supportedAt;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=false)
     */
    protected $type;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $address;

    /**
     * @var string
     *
     * @ORM\Column(type="string", nullable=true)
     */
    protected $postCode;

    /**
     * @var Commune
     *
     * @ORM\ManyToOne(targetEntity="RuchJow\TerritorialUnitsBundle\Entity\Commune")
     * @ORM\JoinColumn(name="commune_id")
     */
    protected $commune;

    /**
     * @var string
     *
     * @ORM\Column(name="search_string", type="string", nullable=false)
     */
    protected $searchString;

    /**
     * @var string
     *
     * @ORM\Column(name="search_result", type="text", nullable=true)
     */
    protected $searchResult;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
     */
    protected $lat;

    /**
     * @var float
     *
     * @ORM\Column(type="float", nullable=true)
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
     * Set name
     *
     * @param string $name
     *
     * @return $this
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
     * Set type
     *
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
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set searchString
     *
     * @param string $searchString
     *
     * @return $this
     */
    public function setSearchString($searchString)
    {
        $this->searchString = $searchString;

        return $this;
    }

    /**
     * Get searchString
     *
     * @return string
     */
    public function getSearchString()
    {
        return $this->searchString;
    }

    /**
     * Set searchResult
     *
     * @param mixed $searchResult
     *
     * @return $this
     */
    public function setSearchResult($searchResult)
    {
        $this->searchResult = json_encode($searchResult);

        return $this;
    }

    /**
     * Get searchResult
     *
     * @return mixed
     */
    public function getSearchResult()
    {
        return json_decode($this->searchResult);
    }

    /**
     * Set lat
     *
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
     * Get lat
     *
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * Set lng
     *
     * @param float $lng
     *
     * @return $this
     */
    public function setLng($lng)
    {
        $this->lng = $lng;

        return $this;
    }

    /**
     * Get lng
     *
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * Set commune
     *
     * @param Commune $commune
     *
     * @return $this
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

    /**
     * Set address
     *
     * @param string $address
     * @return $this
     */
    public function setAddress($address)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address
     *
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set postCode
     *
     * @param string $postCode
     * @return $this
     */
    public function setPostCode($postCode)
    {
        $this->postCode = $postCode;

        return $this;
    }

    /**
     * Get postCode
     *
     * @return string
     */
    public function getPostCode()
    {
        return $this->postCode;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set supportedAt
     *
     * @param \DateTime $supportedAt
     * @return $this
     */
    public function setSupportedAt($supportedAt)
    {
        $this->supportedAt = $supportedAt;

        return $this;
    }

    /**
     * Get supportedAt
     *
     * @return \DateTime
     */
    public function getSupportedAt()
    {
        return $this->supportedAt;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set subtitle
     *
     * @param string $subtitle
     * @return $this
     */
    public function setSubtitle($subtitle)
    {
        $this->subtitle = $subtitle;

        return $this;
    }

    /**
     * Get subtitle
     *
     * @return string
     */
    public function getSubtitle()
    {
        return $this->subtitle;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return $this
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set linkTitle
     *
     * @param string $linkTitle
     * @return $this
     */
    public function setLinkTitle($linkTitle)
    {
        $this->linkTitle = $linkTitle;

        return $this;
    }

    /**
     * Get linkTitle
     *
     * @return string
     */
    public function getLinkTitle()
    {
        return $this->linkTitle;
    }
}
