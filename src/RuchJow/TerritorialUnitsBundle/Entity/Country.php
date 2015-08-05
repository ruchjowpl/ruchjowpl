<?php

namespace RuchJow\TerritorialUnitsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use RuchJow\TerritorialUnitsBundle\Intl\RegionBundle;

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
    protected $code;


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
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Sets Country code
     *
     * @param string $code code in ISO 3166-1 alpha-2 format
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Returns Country name
     *
     * @param string $displayLocale display locale
     *
     * @return string
     */
    public function getName($displayLocale = null)
    {
        $regionBundle = new RegionBundle();

        return $regionBundle->getCountryName($this->code, $displayLocale);
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
            'code' => $this->getCode(),
        );
    }
}
