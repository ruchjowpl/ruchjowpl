<?php

namespace RuchJow\TerritorialUnitsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="RuchJow\TerritorialUnitsBundle\Entity\CountryRepository")
 * @ORM\Table(name="country")
 */
class Country
{

    /**
     * Main country
     *
     * @const MAIN_COUNTRY main country
     */
    const MAIN_COUNTRY = 'PL';

    /**
     * Identifier
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * Country code ISO 3166-1 alpha-2
     *
     * @var string
     *
     * @ORM\Column(length=2, unique=true)
     */
    protected $ISOCountryCode;


    /**
     * Returns Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns Country code ISO 3166-1 alpha-2
     *
     * @return string
     */
    public function getISOCountryCode()
    {
        return $this->ISOCountryCode;
    }

    /**
     * Sets Country code
     *
     * @param string $ISOCountryCode code in ISO 3166-1 alpha-2 format
     */
    public function setISOCountryCode($ISOCountryCode)
    {
        $this->ISOCountryCode = $ISOCountryCode;
    }

    /**
     * Returns array representing country data
     *
     * @return array
     */
    public function toArray()
    {
        return array(
            'id' => $this->getId(),
            'ISOCountryCode' => $this->getISOCountryCode(),
        );
    }
}
