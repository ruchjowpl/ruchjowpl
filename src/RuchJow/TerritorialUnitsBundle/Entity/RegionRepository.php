<?php

namespace RuchJow\TerritorialUnitsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class RegionRepository extends EntityRepository
{
    /**
     * @param     $name
     * @param int $limit
     *
     * @return Region[]
     */
    public function findRegionsByName($name, $limit = 0) {

        $name = trim(preg_replace('/\s+/', ' ', $name));
        $nameParts = explode(' ', $name);

        $qb = $this->createQueryBuilder('r');

        $qb->orderBy('r.name');

        foreach ($nameParts as $key => $part) {
            $qb->andWhere($qb->expr()->like('r.name', ':part_' . $key))
                ->setParameter('part_' . $key, '%' . $part . '%');
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}