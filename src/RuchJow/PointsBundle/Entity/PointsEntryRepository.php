<?php

namespace RuchJow\PointsBundle\Entity;

use Doctrine\ORM\EntityRepository;
use RuchJow\TerritorialUnitsBundle\Entity\Region;
use RuchJow\TerritorialUnitsBundle\Entity\District;

class PointsEntryRepository extends EntityRepository
{
    /**
     * Retrieves and reformats data of users' or organisations' points.
     *
     * @param string|Region|District $territorialUnit
     * @param int $territorialUnitId
     * @return array
     * @throws \Exception
     */
    public function getPointsOfTerritorialSubunits($territorialUnit, $territorialUnitId = null)
    {
        // allow passing instances of objects
        if (is_object($territorialUnit)) {
            try {
                $territorialUnitId = $territorialUnit->getId();
                $territorialUnit = preg_replace('/.*\\\\(\w+)/', '$1', get_class($territorialUnit)); // discard the namespace
            } catch (\Exception $e) {
                throw new \Exception('Invalid object type.');
            }
        }

        $territorialUnit = mb_strtolower($territorialUnit, 'UTF-8');

        $query = $this->createQueryBuilder('p')
            ->select('SUM(p.points) points')
            ->join('p.commune', 'c')
            ->join('c.district', 'd')
            ->join('d.region', 'r')
            ->addSelect('p.type')
            ->addGroupBy('p.type')
            ->orderBy('p.type');

        switch ($territorialUnit) {
            case 'country':
                $kind = 'region';
                $query
                    ->addSelect('r.id')
                    ->addSelect('r.name')
                    ->addGroupBy('r.id')
                    ->addGroupBy('r.name');
                break;

            case 'region':
                $kind = 'district';
                $query
                    ->addSelect('d.id')
                    ->addSelect('d.name')
                    ->addGroupBy('d.id')
                    ->addGroupBy('d.name')
                    ->where('r.id = :id');
                break;

            case 'district':
                $kind = 'commune';
                $query
                    ->addSelect('c.id')
                    ->addSelect('c.name')
                    ->addGroupBy('c.id')
                    ->addGroupBy('c.name')
                    ->where('d.id = :id');
                break;

            case 'commune':
                return array();

            default:
                throw new \Exception('Invalid object type.');
        }

        if ($territorialUnit !== 'country') {
            $query->setParameter('id', (int)$territorialUnitId);
        }

        $points = array();
        $pattern = array('total' => 0, 'kind' => $kind, 'parent_unit_kind' => $territorialUnit, 'parent_unit_id' => (int)$territorialUnitId, 'types' => array());
        foreach ($query->getQuery()->getResult() as $entry) {
            $key = $entry['id'];
            if (!isset($points[$key])) {
                $points[$key] = $pattern;
                $points[$key]['id'] = $entry['id'];
                $points[$key]['name'] = $entry['name'];
            }

            $points[$key]['types'][$entry['type']] = (int)$entry['points'];
            $points[$key]['total'] += (int)$entry['points'];
        }

        return array_values($points);
    }

    /**
     * Retrieves and reformats data of users' or organisations' points.
     *
     * @param string $objectTypes
     * @return array
     * @throws \Exception
     */
    public function getPointsOf($objectTypes)
    {
        $objectTypes = mb_strtolower($objectTypes, 'UTF-8');

        $query = $this->createQueryBuilder('p')
            ->select('SUM(p.points) points')
            ->join('p.commune', 'c')
            ->join('c.district', 'd')
            ->join('d.region', 'r')
            ->addSelect('p.type')
            ->addGroupBy('p.type')
            ->orderBy('p.type');

        switch ($objectTypes) {
            case 'users':
                $kind = 'user';
                $query
                    ->addSelect('c.id AS commune_id')
                    ->addSelect('d.id AS district_id')
                    ->addSelect('r.id AS region_id')
                    ->addGroupBy('c.id')
                    ->addGroupBy('d.id')
                    ->addGroupBy('r.id')

                    ->join('p.user', 'u')
                    ->addSelect('u.id')
                    ->addSelect('u.firstName')
                    ->addSelect('u.lastName')
                    ->addSelect('u.nick')
                    ->addGroupBy('u.id')
                    ->addGroupBy('u.firstName')
                    ->addGroupBy('u.lastName')
                    ->addGroupBy('u.nick');
                break;

            case 'organisations':
                $kind = 'organisation';
                $query
                    ->addSelect('c.id AS commune_id')
                    ->addSelect('d.id AS district_id')
                    ->addSelect('r.id AS region_id')
                    ->addGroupBy('c.id')
                    ->addGroupBy('d.id')
                    ->addGroupBy('r.id')

                    ->join('p.organisation', 'o')
                    ->addSelect('o.id')
                    ->addSelect('o.name')
                    ->addSelect('o.url')
                    ->addGroupBy('o.id')
                    ->addGroupBy('o.name')
                    ->addGroupBy('o.url');
                break;

            default:
                throw new \Exception('Invalid object type.');
        }

        $points = array();
        $pattern = array('total' => 0, 'kind' => $kind, 'types' => array());
        foreach ($query->getQuery()->getResult() as $entry) {
            $key = $entry['id'];
            if (!isset($points[$key])) {
                $points[$key] = $pattern;
                $points[$key]['id'] = $entry['id'];
                $points[$key]['commune_id'] = $entry['commune_id'];
                $points[$key]['district_id'] = $entry['district_id'];
                $points[$key]['region_id'] = $entry['region_id'];

                if ($kind == 'user') {
                    $points[$key]['first_name'] = $entry['firstName'];
                    $points[$key]['last_name'] = $entry['lastName'];
                    $points[$key]['nick'] = $entry['nick'];
                } elseif ($kind == 'organisation') {
                    $points[$key]['name'] = $entry['name'];
                    $points[$key]['url'] = $entry['url'];
                }
            }

            $points[$key]['types'][$entry['type']] = (int)$entry['points'];
            $points[$key]['total'] += (int)$entry['points'];
        }

        return array_values($points);
    }

    public function getBestTerritorialUnits($kind = 'all', $limit = 15)
    {
        $kind = rtrim(mb_strtolower($kind, 'UTF-8'), 's');

        $query = $this->createQueryBuilder('p')
            ->select('SUM(p.points) points')
            ->join('p.commune', 'c')
            ->join('c.district', 'd')
            ->join('d.region', 'r')
            ->addSelect('p.type')
            ->addGroupBy('p.type')
            ->orderBy('points', 'DESC');

        switch ($kind) {
            case 'region':
                $parentKind = 'country';
                $query
                    ->addSelect('0 AS parent_unit_id')
                    ->addSelect('r.id')
                    ->addSelect('r.name')
                    ->addGroupBy('r.id')
                    ->addGroupBy('r.name');
                break;

            case 'district':
                $parentKind = 'region';
                $query
                    ->addSelect('r.id AS parent_unit_id')
                    ->addSelect('d.id')
                    ->addSelect('d.name')
                    ->addGroupBy('r.id')
                    ->addGroupBy('d.id')
                    ->addGroupBy('d.name');
                break;

            case 'commune':
                $parentKind = 'district';
                $query
                    ->addSelect('d.id AS parent_unit_id')
                    ->addSelect('c.id')
                    ->addSelect('c.name')
                    ->addGroupBy('d.id')
                    ->addGroupBy('c.id')
                    ->addGroupBy('c.name');
                break;

            case 'all':
                return array_merge(
                    $this->getBestTerritorialUnits('region',   $limit),
                    $this->getBestTerritorialUnits('district', $limit),
                    $this->getBestTerritorialUnits('commune',  $limit)
                );
        }

        $points = array();
        $pattern = array('total' => 0, 'kind' => $kind, 'parent_unit_kind' => $parentKind, 'types' => array());
        foreach ($query->getQuery()->getResult() as $entry) {
            $key = $entry['id'];
            if (!isset($points[$key])) {
                $points[$key] = $pattern;
                $points[$key]['id'] = $entry['id'];
                $points[$key]['name'] = $entry['name'];
                $points[$key]['parent_unit_id'] = $entry['parent_unit_id'];
            }

            $points[$key]['types'][$entry['type']] = (int)$entry['points'];
            $points[$key]['total'] += (int)$entry['points'];
        }

        usort($points, array($this, 'compareTotalPoints'));
        array_splice($points, $limit);

        return array_values($points);
    }

    public static function compareTotalPoints(&$a, &$b)
    {
        if ($a['total'] == $b['total']) {
            return ($a['id'] < $b['id'])? 1 : -1;
        }

        return ($a['total'] > $b['total'])? 1 : -1;
    }
}