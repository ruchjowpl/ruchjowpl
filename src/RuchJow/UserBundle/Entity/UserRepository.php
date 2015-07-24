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


    /**
     * @param      $search
     * @param bool $byUsername
     * @param bool $byEmail
     * @param int  $limit
     * @param int  $minLen
     * @param bool $minLenAll
     * @param bool $sort
     *
     * @return User[]
     */
    public function searchUsers($search, $byUsername = true, $byEmail = true, $limit = 0, $minLen = 3, $minLenAll = false, $sort = false)
    {

        $search = trim(preg_replace('/\s+/', ' ', $search));
        $searchParts = explode(' ', $search);

        if (empty($searchParts)) {
            return array();
        }



        if ($minLenAll) {
            $minLenOk = true;

            foreach ($searchParts as $part) {
                if (strlen($part) < $minLen) {
                    $minLenOk = false;
                    break;
                }
            }

        } else {
            $minLenOk = false;
            foreach ($searchParts as $part) {
                if (strlen($part) >= $minLen) {
                    $minLenOk = true;
                    break;
                }
            }
        }

        if (!$minLenOk) {
            return array();
        }

        $qb = $this->createQueryBuilder('u');

        if ($sort) {
            $qb->orderBy('u.username');
        }

        $expr = $qb->expr();
        foreach ($searchParts as $key => $part) {

            $conditions = array();

            if ($byUsername) {
                $paramName = 'part_username_' . $key;
                $conditions[] = $expr->like('u.username', ':' . $paramName);
                $qb->setParameter($paramName, '%' . $part . '%');
            }

            if ($byEmail) {
                $paramName = 'part_email_' . $key;
                $conditions[] = $qb->expr()->like('u.emailCanonical', ':' . $paramName);
                $qb->setParameter($paramName, '%' . $part . '%');
            }

            $where = '(' . implode(' OR ', $conditions) . ')';
            $qb->andWhere($where);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->getResult();
    }
}