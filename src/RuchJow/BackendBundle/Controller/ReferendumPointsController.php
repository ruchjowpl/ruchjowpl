<?php

namespace RuchJow\BackendBundle\Controller;


use RuchJow\AppBundle\Entity\ReferendumPoint;
use RuchJow\TransferujPlBundle\Service\PaymentManager;
use RuchJow\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package RuchJow\BackendBundle\Controller
 *
 * @Route("/")
 */
class ReferendumPointsController extends ModelController
{


    /**
     * @return Response
     *
     * @Route("/cif/referendumPoints/list", name="backend_cif_referendum_points_list", options={"expose": true})
     */
    public function listAction()
    {
        $repo = $this->getDoctrine()->getRepository('RuchJowAppBundle:ReferendumPoint');

        /** @var User $user */
        $referendumPoints = $repo->findAll();
        $ret = array();
        foreach ($referendumPoints as $point) {
            $ret[] = $this->referendumPointToArray($point);
        }

        return $this->createJsonResponse(array(
            'status' => 'success',
            'data'   => $ret
        ));
    }

    /**
     * @return Response
     *
     * @Route("/cif/referendumPoints/update", name="backend_cif_referendum_points_update", options={"expose": true})
     * @Method("POST")
     */
    public function updateAction()
    {
        $em = $this->getDoctrine()->getManager();

        $error = $this->validateRequestJson(
            array(
                'type'     => 'array',
                'children' => array(
                    'id'    => array('type' => 'integer', 'optional' => true),
                    'title' => array('type' => 'string',),
                    'subtitle' => array('type' => 'string',),
                    'description' => array('type' => 'string',),
                    'lat' => array('type' => 'decimal'),
                    'lng' => array('type' => 'decimal'),
                    'communeId' => array('type' => 'entityId', 'entity' => 'RuchJowTerritorialUnitsBundle:Commune')
                )
            ),
            $data
        );

        if ($error) {
            return $this->createJsonErrorResponse(array(
                'status' => 'error',
                'message' => $error['message'],
            ), 400);
        }

        if (isset($data['id'])) {
            $repo = $em->getRepository('RuchJowAppBundle:ReferendumPoint');

            $point = $repo->find($data['id']);

            if (!$point) {
                return $this->createJsonErrorResponse(array(
                    'status' => 'error',
                    'message' => 'Referendum point could not be found',
                ), 404);
            }
        } else {
            $point = new ReferendumPoint();
        }

        $communeRepo = $em->getRepository('RuchJowTerritorialUnitsBundle:Commune');
        $commune = $communeRepo->find($data['communeId']);

        if (!$commune) {
            return $this->createJsonErrorResponse(array(
                'status' => 'error',
                'message' => 'Commune could not be found',
            ), 400);
        }

        $point->setTitle($data['title'])
            ->setSubtitle($data['subtitle'])
            ->setDescription($data['description'])
            ->setLat($data['lat'])
            ->setLng($data['lng'])
            ->setCommune($commune);

        $em->persist($point);
        $em->flush();

        return $this->createJsonResponse(array(
            'status' => 'success',
            'data'   => $this->referendumPointToArray($point),
        ));
    }

    /**
     * @param ReferendumPoint $point
     *
     * @return array
     */
    protected function referendumPointToArray(ReferendumPoint $point)
    {
        $id = $point->getId();
        $entry = array(
            'id' => $id,
            'title' => $point->getTitle(),
            'subtitle' => $point->getSubtitle(),
            'description' => $point->getDescription(),
            'lat' => $point->getLat(),
            'lng' => $point->getLng(),
            'commune' => null
        );

        if ($commune = $point->getCommune()) {
            $entry['commune'] = array(
                'id' => $commune->getId(),
                'name' => $commune->getName(),
                'district' => $commune->getDistrict()->getName(),
                'region' => $commune->getDistrict()->getRegion()->getName(),
            );
        }

        return $entry;
    }

}
