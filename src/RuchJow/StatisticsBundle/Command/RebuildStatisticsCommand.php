<?php

namespace RuchJow\StatisticsBundle\Command;

use Doctrine\Common\Persistence\ObjectManager;
use RuchJow\TransferujPlBundle\Service\PaymentManager;
use RuchJow\PointsBundle\Services\PointsManager;
use RuchJow\StatisticsBundle\Entity\StatisticManager;
use RuchJow\StatisticsBundle\RuchJowStatisticsBundle;
use RuchJow\TerritorialUnitsBundle\Entity\Commune;
use RuchJow\TerritorialUnitsBundle\Entity\CommuneRepository;
use RuchJow\UserBundle\Entity\UserRepository;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Update prices using external service, i.e. Koala SOAP API.
 *
 * @package RuchJow\StatisticsBundle\Command
 */
class RebuildStatisticsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('ruchjow:statistics:rebuild')
            ->setDescription('Rebuilds statistics');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        /** @var StatisticManager $statisticManager */
        $statisticManager = $container->get('ruch_jow_statistics.statistic_manager');
        $statisticRepo    = $statisticManager->getRepository();

        /** @var ObjectManager $entityManager */
        $entityManager = $container->get('doctrine')->getManager();

        // USER REPOSITORY
        /** @var UserRepository $userRepo */
        $userRepo = $entityManager->getRepository('RuchJowUserBundle:User');

        // SUPPORTING USERS: ALL
        $qb         = $userRepo->createQueryBuilder('u');
        $supporters = $qb
            ->select('count(u.supports)')
            ->where($qb->expr()->eq('u.supports', 1))
            ->getQuery()
            ->getSingleScalarResult();

        $statisticManager->setStatisticValue(RuchJowStatisticsBundle::STAT_SUPPORTING_USERS, intval($supporters), true);

        // SUPPORTING USERS: LOCAL GOVERNMENT
        $qb         = $userRepo->createQueryBuilder('u');
        $supporters = $qb
            ->select('count(u.supports)')
            ->where($qb->expr()->eq('u.supports', 1))
            ->andWhere($qb->expr()->eq('u.localGov', 1))
            ->getQuery()
            ->getSingleScalarResult();

        $statisticManager->setStatisticValue(RuchJowStatisticsBundle::STAT_SUPPORTING_USERS_LOCAL_GOV, intval($supporters), true);

        // PAYMENT REPOSITORY
        /** @var PaymentManager $paymentManager */
        $paymentManager = $container->get('ruch_jow_transferuj_pl.payment_manager');
        $paymentRepo    = $paymentManager->getRepository();

        // DONATIONS
        $qb        = $paymentRepo->createQueryBuilder('p');
        $donations = $qb
            ->select('count(p.id) cnt, sum(p.paid) paid')
            ->where($qb->expr()->eq('p.type', ':type'))
            ->setParameter('type', 'donation')
            ->andWhere($qb->expr()->eq('p.status', ':status'))
            ->setParameter('status', 'TRUE')
            ->getQuery()
            ->getSingleResult();

        $cnt  = intval($donations['cnt']);
        $paid = null !== $donations['paid'] ? round(floatval($donations['paid']), 2) : .0;
        $avg  = $cnt ? round($paid / $cnt, 2) : .0;

        $statisticManager->setStatisticValue(RuchJowStatisticsBundle::STAT_DONATIONS_COUNT, $cnt, true);
        $statisticManager->setStatisticValue(RuchJowStatisticsBundle::STAT_DONATIONS, $paid, true);
        $statisticManager->setStatisticValue(RuchJowStatisticsBundle::STAT_DONATIONS_AVG, $avg, true);


        // POINTS REPOSITORY

        /** @var PointsManager $pointsManager */
        $pointsManager = $container->get('ruch_jow_points.points_manager');
        $pointsRepo    = $pointsManager->getRepository();

        // TOTAL POINTS
        $points = $pointsRepo->createQueryBuilder('p')
            ->select('coalesce(sum(p.points), 0) points')
            ->getQuery()
            ->getSingleScalarResult();

        $statisticManager->setStatisticValue(
            RuchJowStatisticsBundle::STAT_POINTS_TOTAL,
            intval($points),
            true
        );

        // USER POINTS

        // Delete all user points statistics.
        $qb = $statisticRepo->createQueryBuilder('s');
        $qb
            ->delete()
            ->where($qb->expr()->eq('s.name', ':name'))
            ->setParameter(':name', RuchJowStatisticsBundle::STAT_POINTS_USER)
            ->getQuery()
            ->execute();

        // Get user's points
        $qb = $pointsRepo->createQueryBuilder('p');
        $results = $qb
            ->join('p.user', 'u')
            ->select('u.id user_id, sum(p.points) points')
            ->groupBy('u.id')
            ->getQuery()
            ->iterate();

        // Rebuild user statistics
        while ($row = $results->next()) {
            $row    = $row[$results->key()];
            $uid    = intval($row['user_id']);
            $points = intval($row['points']);

            $statisticManager->setStatisticValue(
                array(
                    RuchJowStatisticsBundle::STAT_POINTS_USER,
                    $uid
                ),
                $points,
                true
            );
            $entityManager->clear();
        }


        // COMMUNE POINTS
        /** @var CommuneRepository $communeRepo */
        $communeRepo = $entityManager->getRepository('RuchJowTerritorialUnitsBundle:Commune');

        // Delete all commune, district and region points statistics.
        $qb = $statisticRepo->createQueryBuilder('s');
        $qb
            ->delete()
            ->where($qb->expr()->in('s.name', ':names'))
            ->setParameter(':names', array(
                RuchJowStatisticsBundle::STAT_POINTS_COMMUNE,
                RuchJowStatisticsBundle::STAT_POINTS_DISTRICT,
                RuchJowStatisticsBundle::STAT_POINTS_REGION,
            ))
            ->getQuery()
            ->execute();

        // Get commune's points
        $qb = $pointsRepo->createQueryBuilder('p');
        $results = $qb
            ->join('p.commune', 'c')
            ->select('c.id commune_id, sum(p.points) points')
            ->groupBy('c.id')
            ->getQuery()
            ->iterate();

        // Rebuild user statistics
        while ($row = $results->next()) {
            $row    = $row[$results->key()];
            $cid    = intval($row['commune_id']);
            $points = intval($row['points']);

            /** @var Commune $commune */
            $commune  = $communeRepo->find($cid);
            $district = $commune->getDistrict();
            $region   = $district->getRegion();

            $statisticManager->setStatisticValue(
                array(
                    RuchJowStatisticsBundle::STAT_POINTS_COMMUNE,
                    $cid
                ),
                $points
            );

            $statisticManager->incStatistic(
                array(
                    RuchJowStatisticsBundle::STAT_POINTS_DISTRICT,
                    $district->getId()
                ),
                $points
            );

            $statisticManager->incStatistic(
                array(
                    RuchJowStatisticsBundle::STAT_POINTS_REGION,
                    $region->getId()
                ),
                $points
            );

            $entityManager->flush();
            $entityManager->clear();
        }


        // ORGANISATION POINTS

        // Delete all user points statistics.
        $qb = $statisticRepo->createQueryBuilder('s');
        $qb
            ->delete()
            ->where($qb->expr()->eq('s.name', ':name'))
            ->setParameter(':name', RuchJowStatisticsBundle::STAT_POINTS_ORGANISATION)
            ->getQuery()
            ->execute();

        // Get user's points
        $qb = $pointsRepo->createQueryBuilder('p');
        $results = $qb
            ->join('p.organisation', 'o')
            ->select('o.id org_id, sum(p.points) points')
            ->groupBy('o.id')
            ->getQuery()
            ->iterate();

        // Rebuild user statistics
        while ($row = $results->next()) {
            $row    = $row[$results->key()];
            $oid    = intval($row['org_id']);
            $points = intval($row['points']);

            $statisticManager->setStatisticValue(
                array(
                    RuchJowStatisticsBundle::STAT_POINTS_ORGANISATION,
                    $oid
                ),
                $points,
                true
            );
            $entityManager->clear();
        }
    }
}