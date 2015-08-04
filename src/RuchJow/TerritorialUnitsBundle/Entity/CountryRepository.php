<?php

namespace RuchJow\TerritorialUnitsBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class CountryRepository
 * @package RuchJow\TerritorialUnitsBundle\Entity
 */
class CountryRepository extends EntityRepository
{
    /**
     * @param $code
     *
     * @return null|Country
     */
    public function findOneByCode($code) {

        return $this->findOneBy(array('code'=>$code));
    }
}