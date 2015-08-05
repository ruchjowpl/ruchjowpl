<?php

namespace RuchJow\TerritorialUnitsBundle\Intl;

use Symfony\Component\Intl\Intl;
use Symfony\Component\Intl\ResourceBundle\RegionBundleInterface;

/**
 * Class RegionBundle
 * @package RuchJow\TerritorialUnitsBundle\Intl
 */
class RegionBundle implements RegionBundleInterface
{
    /**
     * {@inheritdoc}
     */
    public function getCountryName($country, $displayLocale = null)
    {
        return Intl::getRegionBundle()->getCountryName($country, $displayLocale);
    }

    /**
     * {@inheritdoc}
     */
    public function getCountryNames($displayLocale = null)
    {
        return Intl::getRegionBundle()->getCountryNames($displayLocale);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocales()
    {
        return Intl::getRegionBundle()->getLocales();
    }

    public function findCountriesByName($query, $displayLocale = null)
    {
        $countries =$this->getCountryNames();
        $countriesMatchingQuery = array();

        foreach ($countries as $countryCode=>$countryName) {
            if (stripos($countryName, $query) !== false) {
                $countriesMatchingQuery[$countryCode]=$countryName;
            }
        }

        return $countriesMatchingQuery;
    }
}
