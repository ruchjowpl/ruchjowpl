<?php

namespace RuchJow\StatisticsBundle\Controller;

use RuchJow\LocalGovBundle\Entity\SupportRepository;
use RuchJow\StatisticsBundle\RuchJowStatisticsBundle;
use RuchJow\TerritorialUnitsBundle\Entity\GeoShapeRepository;
use RuchJow\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\Response;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Class DataController
 *
 * @package RuchJow\StatisticsBundle\Controller
 *
 * @Route("")
 */
class DataController extends ModelController
{
    /**
     * @return Response
     *
     * @Route("/basic_statistics", name="statistics_ajax_basic", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     */
    public function getBasicStatisticsAction()
    {
        $data = array();

        $statisticManager = $this->getStatisticManager();
        $userManager      = $this->getUserManager();
        $paymentManager   = $this->getPaymentManager();

        /** @var SupportRepository $localGovSupportRepo */
        $localGovSupportRepo = $this->getDoctrine()->getManager()->getRepository('RuchJowLocalGovBundle:Support');


        $data['supportersCnt']         = $statisticManager->getStatisticValue(RuchJowStatisticsBundle::STAT_SUPPORTING_USERS, 0);
//        $data['supportersLocalGovCnt'] = $statisticManager->getStatisticValue(RuchJowStatisticsBundle::STAT_SUPPORTING_USERS_LOCAL_GOV, 0);

        $data['supportersCnt7d']         = $userManager->getActiveUsersCount('P7D');
//        $data['supportersLocalGovCnt7d'] = $userManager->getActiveUsersCount('P7D', true);

        $data['localGovSupportCnt'] = $localGovSupportRepo->count();

        $data['donations']    = $statisticManager->getStatisticValue(RuchJowStatisticsBundle::STAT_DONATIONS, 0);
        $data['donationsCnt'] = $statisticManager->getStatisticValue(RuchJowStatisticsBundle::STAT_DONATIONS_COUNT, 0);
        $data['donationsAvg'] = $statisticManager->getStatisticValue(RuchJowStatisticsBundle::STAT_DONATIONS_AVG, 0);

        $donation7dStats        = $paymentManager->getPaymentStats('P7D', 'donation');
        $data['donations7d']    = $donation7dStats['total'];
        $data['donationsCnt7d'] = $donation7dStats['cnt'];
        $data['donationsAvg7d'] = $donation7dStats['cnt'] ? round($donation7dStats['total'] / $donation7dStats['cnt'], 2) : 0;

        $data['pointsTotal'] = $statisticManager->getStatisticValue(RuchJowStatisticsBundle::STAT_POINTS_TOTAL);

        return $this->createJsonResponse($data);
    }

    /**
     * @return Response
     *
     * @Route("/user_statistics", name="statistics_ajax_user", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     */
    public function getUserStatistics()
    {
        $statisticManager = $this->getStatisticManager();

        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->createJsonErrorResponse('User not found.');
        }

        return $this->createJsonResponse(
            $statisticManager->getUserStats($user)
        );

//        $commune = $user->getCommune();
//        $org     = $user->getOrganisation();
//
//
//
//        // Init statistics array to be returned.
//        $data = array();
//
//
//        // POINTS
//        // Points: USER
//        $data['pointsUser'] = $statisticManager->getStatisticValue(
//            array(
//                RuchJowStatisticsBundle::STAT_POINTS_USER,
//                $user->getId()
//            ),
//            0
//        );
//
//        // Points: ORGANISATION
//        if ($org) {
//            $data['pointsOrganisation'] = $statisticManager->getStatisticValue(
//                RuchJowStatisticsBundle::STAT_POINTS_ORGANISATION
//                . $org->getId(),
//                0
//            );
//        }
//
//
//        // Points: TU
//        if ($commune) {
//            $data['pointsCommune']  = $statisticManager->getStatisticValue(
//                array(
//                    RuchJowStatisticsBundle::STAT_POINTS_COMMUNE,
//                    $commune->getId()
//                ),
//                0
//            );
//            $data['pointsDistrict'] = $statisticManager->getStatisticValue(
//                array(
//                    RuchJowStatisticsBundle::STAT_POINTS_DISTRICT,
//                    $commune
//                        ->getDistrict()
//                        ->getId(),
//                ),
//                0
//            );
//            $data['pointsRegion']   = $statisticManager->getStatisticValue(
//                array(
//                    RuchJowStatisticsBundle::STAT_POINTS_REGION,
//                    $commune
//                        ->getDistrict()
//                        ->getRegion()
//                        ->getId(),
//                ),
//                0
//            );
//        }
//
//
//        // RANKS
//        // Ranks: USER
//        $data['ranksUser'] = array(
//            'country' => $statisticManager->getUserSingleRank($data['pointsUser']),
//        );
//
//        if ($commune) {
//            $data['ranksUser']['region'] =
//                $statisticManager->getUserSingleRank(
//                    $data['pointsUser'],
//                    array(
//                        'type' => 'region',
//                        'id'   => $commune->getDistrict()->getRegion()->getId(),
//                    )
//                );
//
//            $data['ranksUser']['district'] =
//                $statisticManager->getUserSingleRank(
//                    $data['pointsUser'],
//                    array(
//                        'type' => 'district',
//                        'id'   => $commune->getDistrict()->getId(),
//                    )
//                );
//
//            $data['ranksUser']['commune'] =
//                $statisticManager->getUserSingleRank(
//                    $data['pointsUser'],
//                    array(
//                        'type' => 'commune',
//                        'id'   => $commune->getId(),
//                    )
//                );
//        }
//
//
//        // Ranks TU
//        if ($commune) {
//            $data['ranksTU'] = array();
//
//            $data['ranksTU']['commune'] = array(
//                'country' => $statisticManager->getTUSingleRank(
//                    $data['pointsCommune'],
//                    'commune'
//                ),
//                'region' => $statisticManager->getTUSingleRank(
//                    $data['pointsCommune'],
//                    'commune',
//                    array (
//                        'type' => 'region',
//                        'id'   => $commune->getDistrict()->getRegion()->getId(),
//                    )
//                ),
//                'district' => $statisticManager->getTUSingleRank(
//                    $data['pointsCommune'],
//                    'commune',
//                    array (
//                        'type' => 'district',
//                        'id'   => $commune->getDistrict()->getId(),
//                    )
//                ),
//            );
//
//            $data['ranksTU']['district'] = array(
//                'country' => $statisticManager->getTUSingleRank(
//                    $data['pointsDistrict'],
//                    'district'
//                ),
//                'region' => $statisticManager->getTUSingleRank(
//                    $data['pointsDistrict'],
//                    'district',
//                    array (
//                        'type' => 'region',
//                        'id'   => $commune->getDistrict()->getRegion()->getId(),
//                    )
//                ),
//            );
//
//            $data['ranksTU']['region'] = array(
//                'country' => $statisticManager->getTUSingleRank(
//                    $data['pointsRegion'],
//                    'region'
//                ),
//            );
//        }
//
//        return $this->createJsonResponse($data);
    }

    /**
     * @return Response
     *
     * @Route("/unit_statistics", name="ranks_ajax_unit_statistics", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     */
    public function getUnitStatistics()
    {

        $error = $this->validateRequestJson(
            array(
                'type'     => 'array',
                'children' => array(
                    'type' => array(
                        'type' => 'string',
                    ),
                    'id'   => array(
                        'type'     => 'int',
                        'optional' => true,
                    )
                ),
            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        /** @var GeoShapeRepository $repo */
        $repo = $this->getDoctrine()->getRepository('RuchJowTerritorialUnitsBundle:GeoShape');
        try {
            $shape = $repo->findOneByTypeAndId($data['type'], isset($data['id']) ? $data['id'] : null);
        } catch (\Exception $e) {
            return $this->createJsonErrorResponse($e->getMessage());
        }

        $children = $shape->getChildren();

        $ret = array();
        foreach ($children as $child) {
            $id       = $child->getTerritorialUnitId();
            $ret[$id] = array(
                'total' => $this->getStatisticManager()->getStatisticValue(array(
                    'statistics.points.' . $child->getType(),
                    $id
                ), 0),
            );
        }

        return $this->createJsonResponse($ret);
    }

    /**
     * @return Response
     *
     * @Route("/ranks", name="ranks_ajax_ranks", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     */
    public function getRanks()
    {
        $error = $this->validateRequestJson(
            array(
                'type'     => 'array',
                'children' => array(
                    'type'  => array(
                        'type' => 'string',
                        'in'   => array('user', 'region', 'district', 'commune', 'organisation'),
                    ),
                    'level' => array(
                        'type'     => 'array',
                        'optional' => true,
                        'children' => array(
                            'type' => array(
                                'type' => 'string', // country|region|district|commune
                                'in'   => array('country', 'region', 'district', 'commune'),
                            ),
                            'id'   => array(
                                'type'     => 'int',
                                'optional' => true,
                            )
                        ),
                    ),
                    'limit' => array(
                        'type' => 'int',
                        '>'    => 0,
                        '<='   => 100
                    ),
                    'page'  => array(
                        'type'     => 'int',
                        '>'        => 0,
                        'optional' => true,
                    ),
                ),

            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['msg']);
        }

        if (!isset($data['level'])) {
            $data['level'] = array(
                'type' => 'country'
            );
        }
        if (!isset($data['level']['id'])) {
            $data['level']['id'] = null;
        }

        if ($data['level']['type'] !== 'country' && $data['level']['id'] === null) {
            return $this->createJsonErrorResponse('Incorrect level id.');
        }

        $i = 0;

        $levelsHierarchy = array(
            'country'      => ++$i,
            'organisation' => ++$i,
            'region'       => ++$i,
            'district'     => ++$i,
            'commune'      => ++$i,
            'user'         => ++$i,
        );

        if ($levelsHierarchy[$data['type']] <= $levelsHierarchy[$data['level']['type']]) {
            return $this->createJsonErrorResponse(
                'Rank of ' . $data['type'] . 's on the level of ' . $data['level']['type'] . ' is illogical.'
            );
        }

        if (!isset($data['page'])) {
            $data['page'] = 1;
        }


        /** @var User $user */
        $user = $this->getUser();

        switch ($data['type']) {
            case 'user':
                $ret = $this->getStatisticManager()->getUserRank(
                    $data['level'],
                    $data['limit'],
                    $data['page'],
                    $user
                );

                return $this->createJsonResponse($ret);

            case 'organisation':
                $ret = $this->getStatisticManager()->getOrganisationRank(
                    $data['limit'],
                    $data['page'],
                    $user && $user->getOrganisation()
                        ? $user->getOrganisation()
                        : null
                );

                return $this->createJsonResponse($ret);

            case 'region':
                $ret = $this->getStatisticManager()->getRegionRank(
                    $data['limit'],
                    $data['page'],
                    $user && $user->getCommune()
                        ? $user->getCommune()->getDistrict()->getRegion()
                        : null
                );

                return $this->createJsonResponse($ret);

            case 'district':
                $ret = $this->getStatisticManager()->getDistrictRank(
                    $data['level'],
                    $data['limit'],
                    $data['page'],
                    $user && $user->getCommune()
                        ? $user->getCommune()->getDistrict()
                        : null
                );

                return $this->createJsonResponse($ret);

            case 'commune':
                $ret = $this->getStatisticManager()->getCommuneRank(
                    $data['level'],
                    $data['limit'],
                    $data['page'],
                    $user && $user->getCommune()
                        ? $user->getCommune()
                        : null
                );

                return $this->createJsonResponse($ret);
        }


        return $this->createJsonErrorResponse('Ranking type not implemented.');

    }
}