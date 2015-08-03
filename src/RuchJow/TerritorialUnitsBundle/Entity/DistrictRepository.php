<?php

namespace RuchJow\TerritorialUnitsBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DistrictRepository extends EntityRepository
{
    /**
     * @param     $name
     * @param int $limit
     *
     * @return District[]
     */
    public function findDistrictsByName($name, $limit = 0) {

        $name = trim(preg_replace('/\s+/', ' ', $name));
        $nameParts = explode(' ', $name);

        $qb = $this->createQueryBuilder('d');

        $qb->join('d.region', 'r')
            ->addSelect('r')
            ->orderBy('d.name');

        foreach ($nameParts as $key => $part) {
            $qb->andWhere($qb->expr()->like('d.name', ':part_' . $key))
                ->setParameter('part_' . $key, '%' . $part . '%');
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}