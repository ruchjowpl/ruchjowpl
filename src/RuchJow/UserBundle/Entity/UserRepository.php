<?php

namespace RuchJow\UserBundle\Entity;

use Doctrine\ORM\EntityRepository;
use FOS\UserBundle\Util\Canonicalizer;

class UserRepository extends EntityRepository
{

    /**
     *
     * @param array $emails
     * @return array
     */
    public function findEmails($emails)
    {
        $qb = $this->createQueryBuilder('u');

        $qb->select('u.email')
          ->where($qb->expr()->in('u.email', $emails))
          ;

        $result = $qb->getQuery()->getScalarResult();

        return \array_map('current', $result);
    }
}