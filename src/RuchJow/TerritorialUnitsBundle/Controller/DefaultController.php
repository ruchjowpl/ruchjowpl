<?php

namespace RuchJow\TerritorialUnitsBundle\Controller;


use RuchJow\TerritorialUnitsBundle\Entity\Commune;
use RuchJow\TerritorialUnitsBundle\Entity\CommuneRepository;
use RuchJow\TerritorialUnitsBundle\Entity\District;
use RuchJow\TerritorialUnitsBundle\Entity\GeoShapeRepository;
use RuchJow\TerritorialUnitsBundle\Entity\Region;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package RuchJow\TerritorialUnitsBundle\Controller
 *
 * @Route("/")
 */
class DefaultController extends ModelController
{
    /**
     * @return Response
     *
     * @Route("/ajax/communes", name="territorial_units_ajax_communes", options={"expose": true})
     */
    public function indexAction()
    {
        $error = $this->validateRequestJson(
            array('type' => 'string'),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $retArray = array();

        if (preg_match('/^\d\d-?\d{0,3}/', $data)) {

            /** @var CommuneRepository $repo */
            $repo = $this->getDoctrine()
                ->getRepository('RuchJowTerritorialUnitsBundle:Commune');

            $communes = $repo->findCommunesByPostCode($data, 8);

            $retArray = $this->prepareCommunesArray($communes);

        } elseif (preg_match('/^(?=.*[A-Za-zĘÓŁŚĄŻŹĆŃęółśążźćń]{2,})([A-Za-zĘÓŁŚĄŻŹĆŃęółśążźćń\s])*$/', $data)) {

            /** @var CommuneRepository $repo */
            $repo = $this->getDoctrine()
                ->getRepository('RuchJowTerritorialUnitsBundle:Commune');

            $communes = $repo->findCommunesByName($data, 8);

            $retArray = $this->prepareCommunesArray($communes);
        }

        return $this->createJsonResponse($retArray);
    }

    /**
     * @return Response
     *
     * @Route("/ajax/search/tu", name="territorial_units_ajax_search_tu", options={"expose": true})
     */
    public function searchTerritorialUnits()
    {
        $limit = 8;
        $findDistricts = true;
        $findRegions = true;

        $error = $this->validateRequestJson(
            array('type' => 'string'),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse($error['message']);
        }

        $retArray = array();
        $regions = array();
        $districts = array();


        if (preg_match('/^\d\d-?\d{0,3}/', $data)) {

            /** @var CommuneRepository $repo */
            $repo = $this->getDoctrine()->getRepository('RuchJowTerritorialUnitsBundle:Commune');

            $communes = $repo->findCommunesByPostCode($data, $limit);

            $i = 0;
            foreach ($communes as $commune) {
                if (++$i === $limit) { break; }

                $district = $commune->getDistrict();
                if ($findDistricts && !isset($districts[$district->getId()])) {
                    $districts[$district->getId()] = $district;
                    if (++$i === $limit) { break; }
                }

                $region = $district->getRegion();
                if ($findRegions && !isset($regions[$region->getId()])) {
                    $regions[$region->getId()] = $region;
                    if (++$i === $limit) { break; }
                }
            }

            $retArray = $this->prepareTUArray(array_merge($communes, $districts, $regions));

        } elseif (preg_match('/^(?=.*[A-Za-zĘÓŁŚĄŻŹĆŃęółśążźćń]{2,})([A-Za-zĘÓŁŚĄŻŹĆŃęółśążźćń\s])*$/', $data)) {

            /** @var CommuneRepository $repo */
            $repo = $this->getDoctrine()
                ->getRepository('RuchJowTerritorialUnitsBundle:Commune');



            $communes = $repo->findCommunesByName($data, $limit);
            $i = count($communes);

            if ($findDistricts && $limit > $i) {
                $districtRepo = $this->getDoctrine()->getRepository('RuchJowTerritorialUnitsBundle:District');
                $districts = $districtRepo->findDistrictsByName($data, $limit - $i);
                $i += count($districts);
            }

            if ($findRegions && $limit > $i) {
                $regionsRepo = $this->getDoctrine()->getRepository('RuchJowTerritorialUnitsBundle:Region');
                $regions = $regionsRepo->findRegionsByName($data, $limit - $i);
            }

            $retArray = $this->prepareTUArray(array_merge($communes, $districts, $regions));
        }

        return $this->createJsonResponse(array(
            'status' => 'success',
            'data' => $retArray
        ));
    }


    /**
     * @Route("/geo_shapes", name="territorial_units_geo_shapes", options={"expose": true})
     * @Method("POST")
     */
    public function getShapes()
    {

        $error = $this->validateRequestJson(
            array(
                'type' => 'array',
                'children' => array(
                    'type' => array(
                        'type' => 'string',
                    ),
                    'id' => array(
                        'type' => 'int',
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
            if (!$shape) {
                return $this->createJsonErrorResponse('Shape not found',404);
            }
        } catch (\Exception $e) {
            return $this->createJsonErrorResponse('Problem: '. $e->getMessage());
        }

        return $this->createJsonResponse($shape->toArray(true));
    }

    protected function prepareCommunesArray($communes)
    {
        $retArray = array();
        foreach ($communes as $commune) {
            $retArray[] = array(
                'id'       => $commune->getId(),
                'name'     => $commune->getName(),
                'type'     => $commune->getType(),
                'district' => $commune->getDistrict()->getName(),
                'region'   => $commune->getDistrict()->getRegion()->getName(),
            );
        }

        return $retArray;
    }


    /**
     * @param $tuUnits
     *
     * @return array
     */
    protected function prepareTUArray($tuUnits)
    {
        $retArray = array();
        foreach ($tuUnits as $unit) {

            if ($unit instanceof Commune) {
                $retArray[] = array(
                    'unitType'     => 'commune',
                    'id'       => $unit->getId(),
                    'name'     => $unit->getName(),
                    'type'     => $unit->getType(),
                    'district' => $unit->getDistrict()->getName(),
                    'region'   => $unit->getDistrict()->getRegion()->getName(),
                );
            } elseif ($unit instanceof District) {
                $retArray[] = array(
                    'unitType'     => 'district',
                    'id'       => $unit->getId(),
                    'name'     => $unit->getName(),
                    'region'   => $unit->getRegion()->getName(),
                );
            } elseif ($unit instanceof Region) {
                $retArray[] = array(
                    'unitType'     => 'region',
                    'id'       => $unit->getId(),
                    'name'     => $unit->getName(),
                );
            }
        }

        return $retArray;
    }
}
