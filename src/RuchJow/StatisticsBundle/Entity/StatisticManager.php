<?php

namespace RuchJow\StatisticsBundle\Entity;


use Doctrine\DBAL\Driver\Statement;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use RuchJow\StatisticsBundle\RuchJowStatisticsBundle;
use RuchJow\TerritorialUnitsBundle\Entity\Commune;
use RuchJow\TerritorialUnitsBundle\Entity\District;
use RuchJow\TerritorialUnitsBundle\Entity\Region;
use RuchJow\UserBundle\Entity\Organisation;
use RuchJow\UserBundle\Entity\OrganisationRepository;
use RuchJow\UserBundle\Entity\User;
use RuchJow\UserBundle\Entity\UserManager;
use Symfony\Component\DependencyInjection\Container;

class StatisticManager
{

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var StatisticRepository
     */
    protected $repository;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $socialServices;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    protected function getEntityManager()
    {
        if (!$this->entityManager) {
            $this->entityManager = $this->container->get('doctrine')->getManager();
        }

        return $this->entityManager;
    }


    /**
     * @return StatisticRepository
     */
    public function getRepository()
    {
        if (!$this->repository) {
            $this->repository = $this->getEntityManager()->getRepository('RuchJowStatisticsBundle:Statistic');
        }

        return $this->repository;
    }

    /**
     * @param string|array $name
     *
     * @return Statistic
     */
    public function getStatistic($name)
    {
        if (is_array($name)) {
            $suffix = $name[1];
            $name   = $name[0];
        } else {
            $suffix = '';
        }

        $statistic = $this->getRepository()->find(array('name' => $name, 'suffix' => $suffix));

        if (!$statistic) {
            $statistic = new Statistic();
            $statistic
                ->setName($name)
                ->setSuffix($suffix)
                ->setData(null);
        }

        return $statistic;
    }

    public function getRank($name)
    {
        $value = $this->getStatisticValue($name);
        if (!is_integer($value)) {
            $value = null;
        }

        if (is_array($name)) {
            $name = $name[0];
        }

        $rank = $this->getRepository()->getRank($name, $value);

        return $rank;
    }

    /**
     * @param Statistic $statistic
     * @param bool      $flush
     */
    public function updateStatistic(Statistic $statistic, $flush = false)
    {
        $em = $this->getEntityManager();
        $em->persist($statistic);

        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Shortcut to get statistic value.
     *
     * @param string|array $name
     *
     * @return mixed
     */
    public function getStatisticValue($name, $default = null)
    {
        $ret = $this->getStatistic($name)->getData();

        return null !== $ret ? $ret : $default;
    }

    /**
     * Shortcut to set statistic value.
     *
     * @param string|array $name
     * @param mixed        $value
     * @param bool         $flush
     */
    public function setStatisticValue($name, $value, $flush = false)
    {
        $this->updateStatistic(
            $this->getStatistic($name)->setData($value),
            $flush
        );
    }

    /**
     * @param string|array $name
     * @param float|int    $inc
     * @param bool         $flush
     *
     * @return mixed
     */
    public function incStatistic($name, $inc = 1, $flush = false)
    {


        $stat = $this->getStatistic($name);
        $val  = $stat->getData();
        $stat->setData(is_numeric($val) ? $inc + $val : $inc);
        $this->updateStatistic($stat, $flush);

        return $stat->getData();
    }

    /**
     * @param string $name
     * @param int    $inc
     * @param bool   $flush
     *
     * @return mixed
     */
    public function decStatistic($name, $inc = 1, $flush = false)
    {
        return $this->incStatistic($name, -$inc, $flush);
    }


    /**
     * @param User $user
     * @param bool $incOrgStats
     * @param bool $incTUStats
     *
     * @return array
     */
    public function getUserStats(User $user, $incOrgStats = true, $incTUStats = true)
    {
        $commune = $user->getCommune();
        $org     = $user->getOrganisation();

        // Init statistics array to be returned.
        $data = array();

        // POINTS
        // Points: USER
        $data['pointsUser'] = $this->getStatisticValue(
            array(
                RuchJowStatisticsBundle::STAT_POINTS_USER,
                $user->getId()
            ),
            0
        );

        // Points: ORGANISATION
        if ($incOrgStats && $org) {
            $data['pointsOrganisation'] = $this->getStatisticValue(
                RuchJowStatisticsBundle::STAT_POINTS_ORGANISATION
                . $org->getId(),
                0
            );
        }

        // Points: TU
        if ($incTUStats && $commune) {
            $data['pointsCommune']  = $this->getStatisticValue(
                array(
                    RuchJowStatisticsBundle::STAT_POINTS_COMMUNE,
                    $commune->getId()
                ),
                0
            );
            $data['pointsDistrict'] = $this->getStatisticValue(
                array(
                    RuchJowStatisticsBundle::STAT_POINTS_DISTRICT,
                    $commune
                        ->getDistrict()
                        ->getId(),
                ),
                0
            );
            $data['pointsRegion']   = $this->getStatisticValue(
                array(
                    RuchJowStatisticsBundle::STAT_POINTS_REGION,
                    $commune
                        ->getDistrict()
                        ->getRegion()
                        ->getId(),
                ),
                0
            );
        }

        // RANKS
        // Ranks: USER
        $data['ranksUser'] = array(
            'country' => $this->getUserSingleRank($data['pointsUser']),
        );

        if ($incTUStats && $commune) {
            $data['ranksUser']['region'] =
                $this->getUserSingleRank(
                    $data['pointsUser'],
                    array(
                        'type' => 'region',
                        'id'   => $commune->getDistrict()->getRegion()->getId(),
                    )
                );

            $data['ranksUser']['district'] =
                $this->getUserSingleRank(
                    $data['pointsUser'],
                    array(
                        'type' => 'district',
                        'id'   => $commune->getDistrict()->getId(),
                    )
                );

            $data['ranksUser']['commune'] =
                $this->getUserSingleRank(
                    $data['pointsUser'],
                    array(
                        'type' => 'commune',
                        'id'   => $commune->getId(),
                    )
                );
        }

        // Ranks TU
        if ($incTUStats && $commune) {
            $data['ranksTU'] = array();

            $data['ranksTU']['commune'] = array(
                'country' => $this->getTUSingleRank(
                    $data['pointsCommune'],
                    'commune'
                ),
                'region' => $this->getTUSingleRank(
                    $data['pointsCommune'],
                    'commune',
                    array (
                        'type' => 'region',
                        'id'   => $commune->getDistrict()->getRegion()->getId(),
                    )
                ),
                'district' => $this->getTUSingleRank(
                    $data['pointsCommune'],
                    'commune',
                    array (
                        'type' => 'district',
                        'id'   => $commune->getDistrict()->getId(),
                    )
                ),
            );

            $data['ranksTU']['district'] = array(
                'country' => $this->getTUSingleRank(
                    $data['pointsDistrict'],
                    'district'
                ),
                'region' => $this->getTUSingleRank(
                    $data['pointsDistrict'],
                    'district',
                    array (
                        'type' => 'region',
                        'id'   => $commune->getDistrict()->getRegion()->getId(),
                    )
                ),
            );

            $data['ranksTU']['region'] = array(
                'country' => $this->getTUSingleRank(
                    $data['pointsRegion'],
                    'region'
                ),
            );
        }


        return $data;
    }





    public function getUserSingleRank($points, $level = null, $nextRank = false)
    {

        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        /** @var ClassMetadata $statsMeta */
        $statsMeta = $em->getClassMetadata('RuchJowStatisticsBundle:Statistic');
        /** @var ClassMetadata $userMeta */
        $userMeta = $em->getClassMetadata('RuchJowUserBundle:User');
        /** @var ClassMetadata $communeMeta */
        $communeMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:Commune');
        /** @var ClassMetadata $districtMeta */
        $districtMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:District');
        /** @var ClassMetadata $regionMeta */
        $regionMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:Region');

        $params = array();

        $joinCommune  = 0;
        $joinDistrict = 0;
        $joinRegion   = 0;

        $from               = $statsMeta->getTableName() . ' s' .
            ' join ' . $userMeta->getTableName() . ' u' .
            ' on' .
            ' s.' . $statsMeta->getColumnName('name') . ' = :statName' .
            ' and u.' . $userMeta->getColumnName('id') . ' = s.' . $statsMeta->getColumnName('suffix');
        $params['statName'] = RuchJowStatisticsBundle::STAT_POINTS_USER;

        $where = ' u.' . $userMeta->getColumnName('enabled') . ' = 1';
        $where .= ' and u.' . $userMeta->getColumnName('supports') . ' = 1';

        if (null !== $points) {
            $where .= ' and s.' . $statsMeta->getColumnName('intData')
                . ' ' . ($nextRank ? '>=' : '>') . ' :points';
            $params['points'] = $points;
        }

        switch ($level['type']) {
            case 'region':
                $joinCommune  = 1;
                $joinDistrict = 1;
                $joinRegion   = 1;

                $where .= ' and r.' . $regionMeta->getColumnName('id') . ' = :territorialUnitId';
                $params['territorialUnitId'] = $level['id'];
                break;

            case 'district':
                $joinCommune  = 1;
                $joinDistrict = 1;

                $where .= ' and d.' . $districtMeta->getColumnName('id') . ' = :territorialUnitId';
                $params['territorialUnitId'] = $level['id'];
                break;

            case 'commune':
                $joinCommune = 1;

                $where .= ' and c.' . $communeMeta->getColumnName('id') . ' = :territorialUnitId';
                $params['territorialUnitId'] = $level['id'];
                break;
        }


        if ($joinCommune) {
            $from .=
                ' join ' . $communeMeta->getTableName() . ' c' .
                ' on c.' . $communeMeta->getColumnName('id') . ' = u.' . $userMeta->getSingleAssociationJoinColumnName('commune');
        }

        if ($joinDistrict) {
            $from .=
                ' join ' . $districtMeta->getTableName() . ' d' .
                ' on d.' . $districtMeta->getColumnName('id') . ' = c.' . $communeMeta->getSingleAssociationJoinColumnName('district');
        }

        if ($joinRegion) {
            $from .=
                ' join ' . $regionMeta->getTableName() . ' r' .
                ' on r.' . $regionMeta->getColumnName('id') . ' = d.' . $districtMeta->getSingleAssociationJoinColumnName('region');
        }

        $sql = 'select' .
            ' count(*)' .
            ' from ' . $from .
            ' where ' . $where;

        $stmt = $em->getConnection()->prepare($sql);
        $this->bindParams($params, $stmt);

        $stmt->execute();
        $ret = $stmt->fetch(Query::HYDRATE_SINGLE_SCALAR);

        return $ret[0] + (null === $points ? 0 : 1);
    }


    private function checkRegionLevel($level){
        return !$level || $level['type'] === 'country';
    }

    private function checkDistrictLevel(District $district, $level) {
        return
            $this->checkRegionLevel($level)
            || $level['type'] === 'region'
            && $level['id'] === $district->getRegion()->getId();
    }

    private function checkCommuneLevel(Commune $commune, $level) {
        return
            $this->checkDistrictLevel($commune->getDistrict(), $level)
            || $level['type'] === 'district'
            && $level['id'] === $commune->getDistrict()->getId();
    }

    private function checkUserLevel(User $user, $level)
    {
        if (!$user->isEnabled() || !$user->isSupports()) {
            return false;
        }

        $commune = $user->getCommune();

        return
            $commune !== null
            && $this->checkCommuneLevel($commune, $level)
            || $this->checkRegionLevel($level);
    }


    /**
     * @param array     $level
     * @param int       $limit
     * @param int       $page
     * @param User|null $includeUser
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function getUserRank($level, $limit, $page = 1, User $includeUser = null)
    {
        if ($includeUser && (!$includeUser->isEnabled() || !$includeUser->isSupports())) {
            $includeUser = null;
        }

        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        /** @var ClassMetadata $statsMeta */
        $statsMeta = $em->getClassMetadata('RuchJowStatisticsBundle:Statistic');
        /** @var ClassMetadata $userMeta */
        $userMeta = $em->getClassMetadata('RuchJowUserBundle:User');
        /** @var ClassMetadata $communeMeta */
        $communeMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:Commune');
        /** @var ClassMetadata $districtMeta */
        $districtMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:District');
        /** @var ClassMetadata $regionMeta */
        $regionMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:Region');

        $total      = $this->getUserSingleRank(null, $level);
        $totalPages = ceil($total / $limit);
        $page       = max(min($totalPages, $page), 1);

        $params = array();

        $where = ' u.' . $userMeta->getColumnName('enabled') . ' = 1';
        $where .= ' and u.' . $userMeta->getColumnName('supports') . ' = 1';

        switch ($level['type']) {
            case 'region':
                $where                       .= ' and r.' . $regionMeta->getColumnName('id') . ' = :territorialUnitId';
                $params['territorialUnitId'] = $level['id'];
                break;

            case 'district':
                $where                       .= ' and d.' . $districtMeta->getColumnName('id') . ' = :territorialUnitId';
                $params['territorialUnitId'] = $level['id'];
                break;

            case 'commune':
                $where                       .= ' and c.' . $communeMeta->getColumnName('id') . ' = :territorialUnitId';
                $params['territorialUnitId'] = $level['id'];
                break;
        }

        $sql = 'select' .
            ' s.' . $statsMeta->getColumnName('name') . ' statistic_name,' .
            ' s.' . $statsMeta->getColumnName('suffix') . ' statistic_suffix,' .
            ' s.' . $statsMeta->getColumnName('intData') . ' points,' .
            ' u.' . $userMeta->getColumnName('id') . ' user_id,' .
            ' c.' . $communeMeta->getColumnName('id') . ' commune_id,' .
            ' d.' . $districtMeta->getColumnName('id') . ' district_id,' .
            ' r.' . $regionMeta->getColumnName('id') . ' region_id' .

            ' from' .

            ' ' . $statsMeta->getTableName() . ' s' .
            ' join ' . $userMeta->getTableName() . ' u' .
            ' on' .
            ' s.' . $statsMeta->getColumnName('name') . ' = :statName' .
            ' and u.' . $userMeta->getColumnName('id') . ' = s.' . $statsMeta->getColumnName('suffix') .

            ' left join ' . $communeMeta->getTableName() . ' c' .
            ' on c.' . $communeMeta->getColumnName('id') . ' = u.' . $userMeta->getSingleAssociationJoinColumnName('commune') .

            ' left join ' . $districtMeta->getTableName() . ' d' .
            ' on d.' . $districtMeta->getColumnName('id') . ' = c.' . $communeMeta->getSingleAssociationJoinColumnName('district') .

            ' left join ' . $regionMeta->getTableName() . ' r' .
            ' on r.' . $regionMeta->getColumnName('id') . ' = d.' . $districtMeta->getSingleAssociationJoinColumnName('region') .

            ' where ' . $where .
            ' order by s.' . $statsMeta->getColumnName('intData') . ' desc' .
            ' limit :skip, :limit';

        $params['statName'] = RuchJowStatisticsBundle::STAT_POINTS_USER;
        $params['skip']     = intval($limit * ($page - 1));
        $params['limit']    = $limit;

        $stmt = $em->getConnection()->prepare($sql);
        $this->bindParams($params, $stmt);

        $stmt->execute();
        $rows = $stmt->fetchAll();

        $ret  = array();
        $uIds = array();
        $uMap = array();
        $i    = 0;
        $rank = null;
        foreach ($rows as $row) {
            $uIds[$row['user_id']] = $row['user_id'];

            $points = intval($row['points']);
            if (null === $rank) {
                $rank = array(
                    'points' => $points,
                    'rank'   => $this->getUserSingleRank($points, $level),
                    'cnt'    => null,
                );
            } elseif ($points !== $rank['points']) {
                $rank = array(
                    'points' => $points,
                    'rank'   => $rank['cnt'] === null
                        ? $this->getUserSingleRank($points, $level)
                        : $rank['rank'] + $rank['cnt'],
                    'cnt'    => 0
                );
            }

            if (null !== $rank['cnt']) {
                $rank['cnt']++;
            }

            $ret[$i]               = array(
                'rank'   => $rank['rank'],
                'points' => intval($row['points']),
                'nick'   => '',
                'displayName' => '',
            );
            $uMap[$row['user_id']] = $i;

            $i++;
        }

        /** @var User[] $users */
        $users = $em->getRepository('RuchJowUserBundle:User')->findBy(array('id' => $uIds));

        foreach ($users as $user) {
            $ret[$uMap[$user->getId()]]['nick'] = $user->getNick() ? $user->getNick() : $user->getUsername();
            $ret[$uMap[$user->getId()]]['displayName'] = $user->getDisplayName();
        }

        $highlighted = null;
        if ($includeUser && $this->checkUserLevel($includeUser, $level)) {
            $uId = $includeUser->getId();

            if (isset($uMap[$uId])) {
                $highlighted                     = $ret[$uMap[$uId]];
                $highlighted['relativePosition'] = 0;
                $ret[$uMap[$uId]]['highlighted'] = true;
            } else {
                $points = $this->getStatisticValue(array(
                    RuchJowStatisticsBundle::STAT_POINTS_USER,
                    $includeUser->getId()
                ), 0);

                $highlighted = array(
                    'points'      => $points,
                    'nick'        => $includeUser->getNick() ? $includeUser->getNick() : $includeUser->getUsername(),
                    'displayName' => $includeUser->getDisplayName(),
                    'highlighted' => true,
                );


                $first = reset($ret);
                if ($points >= $first['points']) {
                    $highlighted['relativePosition'] = 1;
                    $highlighted['rank']             = $points == $first['points']
                        ? $first['rank']
                        : $this->getUserSingleRank($points, $level);
                } else {
                    $last                            = end($ret);
                    $highlighted['relativePosition'] = -1;
                    $highlighted['rank']             = $points == $last['points']
                        ? $last['rank']
                        : $this->getUserSingleRank($points, $level);
                }
            }
        }

        return array(
            'total'       => $total,
            'pages'       => $totalPages,
            'page'        => $page,
            'ranking'     => $ret,
            'highlighted' => $highlighted,
        );
    }

    public function getTUSingleRank($points, $type, $level = null, $nextRank = false)
    {

        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        /** @var ClassMetadata $statsMeta */
        $statsMeta = $em->getClassMetadata('RuchJowStatisticsBundle:Statistic');
        /** @var ClassMetadata $communeMeta */
        $communeMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:Commune');
        /** @var ClassMetadata $districtMeta */
        $districtMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:District');
        /** @var ClassMetadata $regionMeta */
        $regionMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:Region');


        $params = array();
        $from   = $statsMeta->getTableName() . ' s';

        switch ($type) {
            case 'commune':
                $params['statName'] = RuchJowStatisticsBundle::STAT_POINTS_COMMUNE;
                $from .=
                    ' join ' . $communeMeta->getTableName() . ' c' .
                    ' on' .
                    ' s.' . $statsMeta->getColumnName('name') . ' = :statName' .
                    ' and c.' . $communeMeta->getColumnName('id') . ' = s.' . $statsMeta->getColumnName('suffix');
                break;

            case 'district':
                $params['statName'] = RuchJowStatisticsBundle::STAT_POINTS_DISTRICT;
                $from .=
                    ' join ' . $districtMeta->getTableName() . ' d' .
                    ' on' .
                    ' s.' . $statsMeta->getColumnName('name') . ' = :statName' .
                    ' and d.' . $districtMeta->getColumnName('id') . ' = s.' . $statsMeta->getColumnName('suffix');

                break;

            case 'region':
                $params['statName'] = RuchJowStatisticsBundle::STAT_POINTS_REGION;
                $from .=
                    ' join ' . $regionMeta->getTableName() . ' r' .
                    ' on' .
                    ' s.' . $statsMeta->getColumnName('name') . ' = :statName' .
                    ' and r.' . $regionMeta->getColumnName('id') . ' = s.' . $statsMeta->getColumnName('suffix');

                break;
        }

        $joinDistrict = 0;
        $joinRegion   = 0;

        if (null === $points) {
            $where = ' 1 = 1';
        } else {
            $where            = ' s.' . $statsMeta->getColumnName('intData') .
                ' ' . ($nextRank ? '>=' : '>') . ' :points';
            $params['points'] = $points;
        }

        if ($level) {
            switch ($type . '_' . $level['type']) {

                /** @noinspection PhpMissingBreakStatementInspection */
                case 'commune_region':
                    // Join to DISTRICT table
                    $joinDistrict = 1;

                case 'district_region':
                    // Join to REGION table
                    $joinRegion = 1;

                    $where .= ' and r.' . $regionMeta->getColumnName('id') . ' = :territorialUnitId';
                    $params['territorialUnitId'] = $level['id'];
                    break;

                case 'commune_district':
                    // Join to DISTRICT table
                    $joinDistrict = 1;

                    $where .= ' and d.' . $districtMeta->getColumnName('id') . ' = :territorialUnitId';
                    $params['territorialUnitId'] = $level['id'];
                    break;
            }
        }

        if ($joinDistrict) {
            $from .= ' join ' . $districtMeta->getTableName() . ' d' .
                ' on d.' . $districtMeta->getColumnName('id') . ' = c.' . $communeMeta->getSingleAssociationJoinColumnName('district');
        }

        if ($joinRegion) {
            $from .= ' join ' . $regionMeta->getTableName() . ' r' .
                ' on r.' . $regionMeta->getColumnName('id') . ' = d.' . $districtMeta->getSingleAssociationJoinColumnName('region');
        }

        $sql = 'select' .
            ' count(*)' .
            ' from ' . $from .
            ' where ' . $where;

        $stmt = $em->getConnection()->prepare($sql);
        $this->bindParams($params, $stmt);

        $stmt->execute();
        $ret = $stmt->fetch(Query::HYDRATE_SINGLE_SCALAR);

        return $ret[0] + (null === $points ? 0 : 1);
    }

    /**
     * @param array()      $level
     * @param int          $limit
     * @param int          $page
     * @param Commune|null $includeCommune
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function getCommuneRank($level, $limit, $page = 1, Commune $includeCommune = null)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        /** @var ClassMetadata $statsMeta */
        $statsMeta = $em->getClassMetadata('RuchJowStatisticsBundle:Statistic');
        /** @var ClassMetadata $communeMeta */
        $communeMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:Commune');
        /** @var ClassMetadata $districtMeta */
        $districtMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:District');
        /** @var ClassMetadata $regionMeta */
        $regionMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:Region');

        $total      = $this->getTUSingleRank(null, 'commune', $level);
        $totalPages = ceil($total / $limit);
        $page       = max(min($totalPages, $page), 1);

        $params = array();

        switch ($level['type']) {
            case 'region':
                $where                       = 'r.' . $regionMeta->getColumnName('id') . ' = :territorialUnitId';
                $params['territorialUnitId'] = $level['id'];
                break;

            case 'district':
                $where                       = 'd.' . $districtMeta->getColumnName('id') . ' = :territorialUnitId';
                $params['territorialUnitId'] = $level['id'];
                break;

            default:
                $where = '1 = 1';
        }

        $sql = 'select' .
            ' s.' . $statsMeta->getColumnName('name') . ' statistic_name,' .
            ' s.' . $statsMeta->getColumnName('suffix') . ' statistic_suffix,' .
            ' s.' . $statsMeta->getColumnName('intData') . ' points,' .
            ' c.' . $communeMeta->getColumnName('id') . ' commune_id,' .
            ' d.' . $districtMeta->getColumnName('id') . ' district_id,' .
            ' r.' . $regionMeta->getColumnName('id') . ' region_id' .

            ' from' .

            ' ' . $statsMeta->getTableName() . ' s' .
            ' join ' . $communeMeta->getTableName() . ' c' .
            ' on' .
            ' s.' . $statsMeta->getColumnName('name') . ' = :statName' .
            ' and c.' . $communeMeta->getColumnName('id') . ' = s.' . $statsMeta->getColumnName('suffix') .

            ' join ' . $districtMeta->getTableName() . ' d' .
            ' on d.' . $districtMeta->getColumnName('id') . ' = c.' . $communeMeta->getSingleAssociationJoinColumnName('district') .

            ' join ' . $regionMeta->getTableName() . ' r' .
            ' on r.' . $regionMeta->getColumnName('id') . ' = d.' . $districtMeta->getSingleAssociationJoinColumnName('region') .

            ' where ' . $where .
            ' order by s.' . $statsMeta->getColumnName('intData') . ' desc' .
            ' limit :skip, :limit';

        $params['statName'] = RuchJowStatisticsBundle::STAT_POINTS_COMMUNE;
        $params['skip']     = intval($limit * ($page - 1));
        $params['limit']    = $limit;

        $stmt = $em->getConnection()->prepare($sql);
        $this->bindParams($params, $stmt);

        $stmt->execute();
        $rows = $stmt->fetchAll();

        $ret  = array();
        $cIds = array();
        $cMap = array();
        $i    = 0;
        $rank = null;
        foreach ($rows as $row) {
            $cIds[$row['commune_id']] = $row['commune_id'];
            $cMap[$row['commune_id']] = $i;

            $points = intval($row['points']);
            if (null === $rank) {
                $rank = array(
                    'points' => $points,
                    'rank'   => $this->getTUSingleRank($points, 'commune', $level),
                    'cnt'    => null,
                );
            } elseif ($points !== $rank['points']) {
                $rank = array(
                    'points' => $points,
                    'rank'   => $rank['cnt'] === null
                        ? $this->getTUSingleRank($points, 'commune', $level)
                        : $rank['rank'] + $rank['cnt'],
                    'cnt'    => 0
                );
            }

            if (null !== $rank['cnt']) {
                $rank['cnt']++;
            }

            $ret[$i] = array(
                'rank'     => $rank['rank'],
                'points'   => intval($row['points']),
                'name'     => '',
                'unitType' => 'commune',
                'unitId'   => intval($row['commune_id']),
            );

            $i++;
        }

        /** @var Commune[] $communes */
        $communes = $em->getRepository('RuchJowTerritorialUnitsBundle:Commune')->findBy(array('id' => $cIds));

        foreach ($communes as $commune) {
            $ret[$cMap[$commune->getId()]]['name'] = $commune->getName();
        }

        $highlighted = null;
        if ($includeCommune && $this->checkCommuneLevel($includeCommune, $level)) {
            $cId = $includeCommune->getId();

            if (isset($cMap[$cId])) {
                $highlighted                     = $ret[$cMap[$cId]];
                $highlighted['relativePosition'] = 0;
                $ret[$cMap[$cId]]['highlighted'] = true;
            } else {
                $points = $this->getStatisticValue(array(
                    RuchJowStatisticsBundle::STAT_POINTS_COMMUNE,
                    $includeCommune->getId()
                ), 0);

                $highlighted = array(
                    'points'   => $points,
                    'name'     => $includeCommune->getName(),
                    'unitType' => 'commune',
                    'unitId'   => intval($cId),
                    'highlighted' => true,
                );

                $first = reset($ret);
                if ($points >= $first['points']) {
                    $highlighted['relativePosition'] = 1;
                    $highlighted['rank']             = $points == $first['points']
                        ? $first['rank']
                        : $this->getTUSingleRank($points, 'commune', $level);
                } else {
                    $last                            = end($ret);
                    $highlighted['relativePosition'] = -1;
                    $highlighted['rank']             = $points == $last['points']
                        ? $last['rank']
                        : $this->getTUSingleRank($points, 'commune', $level);
                }
            }
        }

        return array(
            'total'       => $total,
            'pages'       => $totalPages,
            'page'        => $page,
            'ranking'     => $ret,
            'highlighted' => $highlighted
        );
    }

    /**
     * @param array         $level
     * @param int           $limit
     * @param int           $page
     * @param District|null $includeDistrict
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     * @throws \Doctrine\ORM\Mapping\MappingException
     */
    public function getDistrictRank($level, $limit, $page = 1, District $includeDistrict = null)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        /** @var ClassMetadata $statsMeta */
        $statsMeta = $em->getClassMetadata('RuchJowStatisticsBundle:Statistic');
        /** @var ClassMetadata $districtMeta */
        $districtMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:District');
        /** @var ClassMetadata $regionMeta */
        $regionMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:Region');

        $total      = $this->getTUSingleRank(null, 'district', $level);
        $totalPages = ceil($total / $limit);
        $page       = max(min($totalPages, $page), 1);

        $params = array();

        switch ($level['type']) {
            case 'region':
                $where                       = 'r.' . $regionMeta->getColumnName('id') . ' = :territorialUnitId';
                $params['territorialUnitId'] = $level['id'];
                break;

            default:
                $where = '1 = 1';
        }

        $sql = 'select' .
            ' s.' . $statsMeta->getColumnName('name') . ' statistic_name,' .
            ' s.' . $statsMeta->getColumnName('suffix') . ' statistic_suffix,' .
            ' s.' . $statsMeta->getColumnName('intData') . ' points,' .
            ' d.' . $districtMeta->getColumnName('id') . ' district_id,' .
            ' r.' . $regionMeta->getColumnName('id') . ' region_id' .

            ' from' .

            ' ' . $statsMeta->getTableName() . ' s' .
            ' join ' . $districtMeta->getTableName() . ' d' .
            ' on' .
            ' s.' . $statsMeta->getColumnName('name') . ' = :statName' .
            ' and d.' . $districtMeta->getColumnName('id') . ' = s.' . $statsMeta->getColumnName('suffix') .

            ' join ' . $regionMeta->getTableName() . ' r' .
            ' on r.' . $regionMeta->getColumnName('id') . ' = d.' . $districtMeta->getSingleAssociationJoinColumnName('region') .

            ' where ' . $where .
            ' order by s.' . $statsMeta->getColumnName('intData') . ' desc' .
            ' limit :skip, :limit';

        $params['statName'] = RuchJowStatisticsBundle::STAT_POINTS_DISTRICT;
        $params['skip']     = intval($limit * ($page - 1));
        $params['limit']    = $limit;

        $stmt = $em->getConnection()->prepare($sql);
        $this->bindParams($params, $stmt);

        $stmt->execute();
        $rows = $stmt->fetchAll();

        $ret  = array();
        $dIds = array();
        $dMap = array();
        $i    = 0;
        $rank = null;
        foreach ($rows as $row) {
            $dIds[$row['district_id']] = $row['district_id'];
            $dMap[$row['district_id']] = $i;

            $points = intval($row['points']);
            if (null === $rank) {
                $rank = array(
                    'points' => $points,
                    'rank'   => $this->getTUSingleRank($points, 'district', $level),
                    'cnt'    => null,
                );
            } elseif ($points !== $rank['points']) {
                $rank = array(
                    'points' => $points,
                    'rank'   => $rank['cnt'] === null
                        ? $this->getTUSingleRank($points, 'district', $level)
                        : $rank['rank'] + $rank['cnt'],
                    'cnt'    => 0
                );
            }

            if (null !== $rank['cnt']) {
                $rank['cnt']++;
            }

            $ret[$i] = array(
                'rank'     => $rank['rank'],
                'points'   => intval($row['points']),
                'name'     => '',
                'unitType' => 'district',
                'unitId'   => intval($row['district_id']),
            );

            $i++;
        }

        /** @var District[] $districts */
        $districts = $em->getRepository('RuchJowTerritorialUnitsBundle:District')->findBy(array('id' => $dIds));

        foreach ($districts as $district) {
            $ret[$dMap[$district->getId()]]['name'] = $district->getName();
        }

        $highlighted = null;
        if ($includeDistrict && $this->checkDistrictLevel($includeDistrict, $level)) {
            $dId = $includeDistrict->getId();

            if (isset($dMap[$dId])) {
                $highlighted                     = $ret[$dMap[$dId]];
                $highlighted['relativePosition'] = 0;
                $ret[$dMap[$dId]]['highlighted'] = true;
            } else {
                $points = $this->getStatisticValue(array(
                    RuchJowStatisticsBundle::STAT_POINTS_DISTRICT,
                    $includeDistrict->getId()
                ), 0);

                $highlighted = array(
                    'points'   => $points,
                    'name'     => $includeDistrict->getName(),
                    'unitType' => 'district',
                    'unitId'   => intval($dId),
                    'highlighted' => true,
                );


                $first = reset($ret);
                if ($points >= $first['points']) {
                    $highlighted['relativePosition'] = 1;
                    $highlighted['rank']             = $points == $first['points']
                        ? $first['rank']
                        : $this->getTUSingleRank($points, 'district', $level);
                } else {
                    $last                            = end($ret);
                    $highlighted['relativePosition'] = -1;
                    $highlighted['rank']             = $points == $last['points']
                        ? $last['rank']
                        : $this->getTUSingleRank($points, 'district', $level);
                }
            }
        }

        return array(
            'total'       => $total,
            'pages'       => $totalPages,
            'page'        => $page,
            'ranking'     => $ret,
            'highlighted' => $highlighted
        );
    }

    /**
     * @param int         $limit
     * @param int         $page
     * @param Region|null $includeRegion
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getRegionRank($limit, $page = 1, Region $includeRegion = null)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        /** @var ClassMetadata $statsMeta */
        $statsMeta = $em->getClassMetadata('RuchJowStatisticsBundle:Statistic');
        /** @var ClassMetadata $regionMeta */
        $regionMeta = $em->getClassMetadata('RuchJowTerritorialUnitsBundle:Region');

        $total      = $this->getTUSingleRank(null, 'region');
        $totalPages = ceil($total / $limit);
        $page       = max(min($totalPages, $page), 1);

        $params = array();

        $sql = 'select' .
            ' s.' . $statsMeta->getColumnName('name') . ' statistic_name,' .
            ' s.' . $statsMeta->getColumnName('suffix') . ' statistic_suffix,' .
            ' s.' . $statsMeta->getColumnName('intData') . ' points,' .
            ' r.' . $regionMeta->getColumnName('id') . ' region_id' .

            ' from' .

            ' ' . $statsMeta->getTableName() . ' s' .
            ' join ' . $regionMeta->getTableName() . ' r' .
            ' on' .
            ' s.' . $statsMeta->getColumnName('name') . ' = :statName' .
            ' and r.' . $regionMeta->getColumnName('id') . ' = s.' . $statsMeta->getColumnName('suffix') .

            ' order by s.' . $statsMeta->getColumnName('intData') . ' desc' .
            ' limit :skip, :limit';

        $params['statName'] = RuchJowStatisticsBundle::STAT_POINTS_REGION;
        $params['skip']     = intval($limit * ($page - 1));
        $params['limit']    = $limit;

        $stmt = $em->getConnection()->prepare($sql);
        $this->bindParams($params, $stmt);

        $stmt->execute();
        $rows = $stmt->fetchAll();

        $ret  = array();
        $rIds = array();
        $rMap = array();
        $i    = 0;
        $rank = null;
        foreach ($rows as $row) {
            $rIds[$row['region_id']] = $row['region_id'];
            $rMap[$row['region_id']] = $i;

            $points = intval($row['points']);
            if (null === $rank) {
                $rank = array(
                    'points' => $points,
                    'rank'   => $this->getTUSingleRank($points, 'region'),
                    'cnt'    => null,
                );
            } elseif ($points !== $rank['points']) {
                $rank = array(
                    'points' => $points,
                    'rank'   => $rank['cnt'] === null
                        ? $this->getTUSingleRank($points, 'region')
                        : $rank['rank'] + $rank['cnt'],
                    'cnt'    => 0
                );
            }

            if (null !== $rank['cnt']) {
                $rank['cnt']++;
            }

            $ret[$i] = array(
                'rank'     => $rank['rank'],
                'points'   => intval($row['points']),
                'name'     => '',
                'unitType' => 'region',
                'unitId'   => intval($row['region_id']),
            );


            $i++;
        }

        /** @var Region[] $regions */
        $regions = $em->getRepository('RuchJowTerritorialUnitsBundle:Region')->findBy(array('id' => $rIds));

        foreach ($regions as $region) {
            $ret[$rMap[$region->getId()]]['name'] = $region->getName();
        }

        $highlighted = null;
        if ($includeRegion) {
            $rId = $includeRegion->getId();

            if (isset($rMap[$rId])) {
                $highlighted                     = $ret[$rMap[$rId]];
                $highlighted['relativePosition'] = 0;
                $ret[$rMap[$rId]]['highlighted'] = true;
            } else {
                $points = $this->getStatisticValue(array(
                    RuchJowStatisticsBundle::STAT_POINTS_REGION,
                    $includeRegion->getId()
                ), 0);

                $highlighted = array(
                    'points'   => $points,
                    'name'     => $includeRegion->getName(),
                    'unitType' => 'region',
                    'unitId'   => intval($rId),
                    'highlighted' => true,
                );


                $first = reset($ret);
                if ($points >= $first['points']) {
                    $highlighted['relativePosition'] = 1;
                    $highlighted['rank']             = $points == $first['points']
                        ? $first['rank']
                        : $this->getTUSingleRank($points, 'region');
                } else {
                    $last                            = end($ret);
                    $highlighted['relativePosition'] = -1;
                    $highlighted['rank']             = $points == $last['points']
                        ? $last['rank']
                        : $this->getTUSingleRank($points, 'region');
                }
            }
        }

        return array(
            'total'       => $total,
            'pages'       => $totalPages,
            'page'        => $page,
            'ranking'     => $ret,
            'highlighted' => $highlighted
        );
    }


    public function getOrganisationSingleRank($points, $nextRank = false)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        /** @var ClassMetadata $statsMeta */
        $statsMeta = $em->getClassMetadata('RuchJowStatisticsBundle:Statistic');
        /** @var ClassMetadata $userMeta */
        $orgMeta = $em->getClassMetadata('RuchJowUserBundle:Organisation');

        $params             = array();
        $params['statName'] = RuchJowStatisticsBundle::STAT_POINTS_ORGANISATION;

        if (null === $points) {
            $where = ' 1 = 1';
        } else {
            $where            = ' s.' . $statsMeta->getColumnName('intData') .
                ' ' . ($nextRank ? '>=' : '>') . ' :points';
            $params['points'] = $points;
        }

        $sql = 'select'
            . ' count(id)'
            . ' from'
            . ' ' . $statsMeta->getTableName() . ' s'
            . ' join ' . $orgMeta->getTableName() . ' o'
            . ' on'
            . ' s.' . $statsMeta->getColumnName('name') . ' = :statName'
            . ' and o.' . $orgMeta->getColumnName('id') . ' = s.' . $statsMeta->getColumnName('suffix')
            . ' where ' . $where;

        $stmt = $em->getConnection()->prepare($sql);
        $this->bindParams($params, $stmt);

        $stmt->execute();
        $ret = $stmt->fetch(Query::HYDRATE_SINGLE_SCALAR);

        return $ret[0] + (null === $points ? 0 : 1);
    }

    /**
     * @param int          $limit
     * @param int          $page
     * @param Organisation $includeOrganisation
     *
     * @return array
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getOrganisationRank($limit, $page = 1, Organisation $includeOrganisation = null)
    {
        /** @var EntityManager $em */
        $em = $this->getEntityManager();

        /** @var OrganisationRepository $orgRepo */
        $orgRepo = $em->getRepository('RuchJowUserBundle:Organisation');

        /** @var ClassMetadata $statsMeta */
        $statsMeta = $em->getClassMetadata('RuchJowStatisticsBundle:Statistic');
        /** @var ClassMetadata $userMeta */
        $orgMeta = $em->getClassMetadata('RuchJowUserBundle:Organisation');

        // Total elements, limit, current page.
//        $total = $this->getOrganisationSingleRank(null);
        $total = $orgRepo->getCount();

        if ($includeOrganisation) {
            $limit--;
            $totalPages = max(ceil(($total - 1)/ $limit), 1);
        } else {
            $totalPages = ceil($total/ $limit);
        }

        $page = max(min($totalPages, $page), 1);

        $params = array();
        $where = array();
        if ($includeOrganisation) {
            $where[] = 'o.id <> :includeOrgId';
            $params['includeOrgId'] = $includeOrganisation->getId();
        }

        $sql = 'select' .
            ' s.' . $statsMeta->getColumnName('name') . ' statistic_name,' .
            ' s.' . $statsMeta->getColumnName('suffix') . ' statistic_suffix,' .
            ' coalesce(s.' . $statsMeta->getColumnName('intData') . ', 0) points,' .
            ' o.' . $orgMeta->getColumnName('id') . ' organisation_id' .

            ' from' .

            ' ' . $orgMeta->getTableName() . ' o' .
            ' left join ' . $statsMeta->getTableName() . ' s' .
            ' on' .
            ' s.' . $statsMeta->getColumnName('name') . ' = :statName' .
            ' and o.' . $orgMeta->getColumnName('id') . ' = s.' . $statsMeta->getColumnName('suffix') .

            ($where ? ' where ' . implode(' and ', $where) : '') .

            ' order by s.' . $statsMeta->getColumnName('intData') . ' desc' .
            ' limit :skip, :limit';

        $params['statName'] = RuchJowStatisticsBundle::STAT_POINTS_ORGANISATION;
        $params['skip']     = intval($limit * ($page - 1));
        $params['limit']    = $limit;


        // Execute statement and fetch results.
        $stmt = $em->getConnection()->prepare($sql);
        $this->bindParams($params, $stmt);
        $stmt->execute();
        $rows = $stmt->fetchAll();


        $ret  = array();
        $oIds = array();
        $oMap = array();
        $i    = 0;
        $rank = null;

        if ($includeOrganisation) {
            $incPoints = $this->getStatisticValue(array(
                RuchJowStatisticsBundle::STAT_POINTS_ORGANISATION,
                $includeOrganisation->getId()
            ), 0 );
            $incRank = $this->getOrganisationSingleRank($incPoints);
        }


        foreach ($rows as $row) {
            $oIds[$row['organisation_id']] = $row['organisation_id'];

            $points = intval($row['points']);
            if (null === $rank) {
                $rank = array(
                    'points' => $points,
                    'rank'   => $this->getOrganisationSingleRank($points),
                    'cnt'    => null,
                );

                if (isset($incRank) && isset($incPoints) && $incRank <= $rank['rank']) {
                    $ret[$i++] = array(
                        'rank'        => $incRank,
                        'points'      => $incPoints,
                        'name'        => $includeOrganisation->getName(),
                        'url'         => $includeOrganisation->getUrl(),
                        'highlighted' => true,
                    );

                    unset($incRank);
                }
            } elseif ($points !== $rank['points']) {
                $rank = array(
                    'points' => $points,
                    'rank'   => $rank['cnt'] === null
                        ? $this->getOrganisationSingleRank($points)
                        : $rank['rank'] + $rank['cnt'],
                    'cnt'    => 0
                );

                if (isset($incRank) && isset($incPoints) && $incRank <= $rank['rank']) {
                    $ret[$i++] = array(
                        'rank'        => $incRank,
                        'points'      => $incPoints,
                        'name'        => $includeOrganisation->getName(),
                        'url'         => $includeOrganisation->getUrl(),
                        'highlighted' => true,
                    );

                    if ($incRank == $rank['rank']) {
                        $rank['cnt']++;
                    }

                    unset($incRank);
                }
            }


            if (null !== $rank['cnt']) {
                $rank['cnt']++;
            }

            $ret[$i] = array(
                'rank'   => $rank['rank'],
                'points' => $points,
                'name'   => '',
                'url'    => ''
            );
            $oMap[$row['organisation_id']] = $i;

            $i++;
        }

        if (isset($incRank) && isset($incPoints)) {
            $ret[$i] = array(
                'rank'        => $incRank,
                'points'      => $incPoints,
                'name'        => $includeOrganisation->getName(),
                'url'         => $includeOrganisation->getUrl(),
                'highlighted' => true,
            );
        }


        /** @var Organisation[] $orgs */
        $orgs = $em->getRepository('RuchJowUserBundle:Organisation')->findBy(array('id' => $oIds));

        foreach ($orgs as $org) {
            $ret[$oMap[$org->getId()]]['name'] = $org->getName();
            $ret[$oMap[$org->getId()]]['url']  = $org->getUrl();
            $ret[$oMap[$org->getId()]]['protocol']  = $org->isHttps() ? 'https://' : 'http://';
        }


        $highlighted = null;

        return array(
            'total'       => $total,
            'pages'       => $totalPages,
            'page'        => $page,
            'ranking'     => $ret,
            'highlighted' => $highlighted,
        );
    }

    /**
     * @param $params
     * @param $stmt   Statement
     */
    protected function bindParams($params, $stmt)
    {
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $type = \PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $type = \PDO::PARAM_BOOL;
            } else {
                $type = \PDO::PARAM_STR;
            }

            $stmt->bindValue($key, $value, $type);
        }
    }
}