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

    /**
     * @param string $exclude
     *
     * @return mixed
     */
    public function getCount($exclude = null) {
        $qb = $this->createQueryBuilder('c');

        $qb->select('count(c.id)');

        if ($exclude) {
            $qb->andWhere($qb->expr()->neq('c.code', ':exclude'));
            $qb->setParameter('exclude', $exclude);
        }

        return $qb->getQuery()->getSingleScalarResult();
    }
}