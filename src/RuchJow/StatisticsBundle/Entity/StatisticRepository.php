<?php

namespace RuchJow\StatisticsBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * StatisticRepository
 */
class StatisticRepository extends EntityRepository
{

    public function getRank($name, $value = null)
    {
        $qb = $this->createQueryBuilder('s');

        $qb->select('count(s.suffix
        )')
            ->where($qb->expr()->eq('s.name', ':name'))
            ->setParameter('name', $name);

        if (null !== $value) {
            $qb->andWhere($qb->expr()->gt('s.intData', ':value'))
                ->setParameter('value', $value);
        }

        $ret = $qb->getQuery()->getSingleScalarResult() + 1;

        return $ret;
    }
}
