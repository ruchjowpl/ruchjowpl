<?php

namespace RuchJow\TransferujPlBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * PaymentRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PaymentRepository extends EntityRepository
{

    /**
     * @param null      $isSentMail
     * @param \DateTime $olderThan
     *
     * @return Payment[]
     */
    public function findAllPayDone($isSentMail = null, \DateTime $olderThan = null)
    {
        $qb = $this->createQueryBuilder('payment');
        $qb->where($qb->expr()->isNotNull('payment.date'))
            ->andWhere($qb->expr()->eq('payment.status', ':status'))
            ->setParameter('status', 'TRUE')
            ->andWhere($qb->expr()->eq('payment.error', ':error'))
            ->setParameter('error', false);

        if (null !== $isSentMail) {
            $qb->andWhere($qb->expr()->eq('payment.isSentEmail', ':isSentEmail'))
                ->setParameter('isSentEmail', (bool) $isSentMail);
        }

        if ($olderThan) {
            $qb->andWhere($qb->expr()->lte('payment.date', ':olderThan'))
                ->setParameter('olderThan', $olderThan);
        }

        return $qb->getQuery()->getResult();
    }

}