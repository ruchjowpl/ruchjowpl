<?php

namespace RuchJow\TerritorialUnitsBundle\Controller;


use RuchJow\TerritorialUnitsBundle\Entity\CommuneRepository;
use RuchJow\TerritorialUnitsBundle\Entity\GeoShapeRepository;
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
}
