<?php

namespace RuchJow\UserBundle\Entity;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;

class OrganisationRepository extends EntityRepository
{

    /**
     * @param string $url
     *
     * @return null|Organisation
     */
    public function findOneByUrl($url)
    {
        $url = preg_replace('/^https?:\/\//', '', $url);
        $url = preg_replace('/^www./', '', $url);

        return $this->findOneBy(array('url' => mb_strtolower($url, 'UTF-8')));
    }

    public function findByUrlPart($part, $maxResults = 0)
    {
        $part = preg_replace('/^https?:\/\//', '', $part);
        $part = preg_replace('/^www\./', '', $part);

        $limitResults = ($maxResults > 0);
        $ret = array();
        $excludeIds = array();

        $org = $this->findOneBy(array('url' => $part));
        if ($org) {
            $ret[] = $org;
            $excludeIds[] = $org->getId();

            if ($limitResults) {
                $maxResults--;
            }
        }

        if (!$limitResults || $maxResults) {
            $part = preg_replace('/([%_!])/', '!$1', $part);

            $qb = $this->createQueryBuilder('o');
            $qb->where($qb->expr()->like('o.url', ':part') . ' ESCAPE \'!\'')
                ->setParameter('part',  $part . '_%');

            if (count($excludeIds)) {
                $qb->andWhere($qb->expr()->notIn('o.id', $excludeIds));
            }

            if ($maxResults) {
                $qb->setMaxResults($maxResults);
            }

            /** @var Organisation[] $orgs */
            $orgs = $qb->getQuery()->getResult();

            foreach ($orgs as $org) {
                $excludeIds[] = $org->getId();
                $ret[] = $org;
            }

            if ($limitResults) {
                $maxResults -= count($orgs);
            }


            if (!$limitResults || $maxResults) {
                $qb = $this->createQueryBuilder('o');
                $qb->where($qb->expr()->like('o.url', ':part'))
                    ->setParameter('part', '%_' . $part . '%');

                if (count($excludeIds)) {
                    $qb->andWhere($qb->expr()->notIn('o.id', $excludeIds));
                }

                if ($maxResults) {
                    $qb->setMaxResults($maxResults);
                }

                /** @var Organisation[] $orgs */
                $orgs = $qb->getQuery()->getResult();
                $ret  = array_merge($ret, $orgs);
            }
        }

        return $ret;
    }


    public function getCount() {
        $qb = $this->createQueryBuilder('o');

        return $qb->select('count(o.id)')->getQuery()->getSingleScalarResult();
    }
}