<?php

namespace RuchJow\BackendBundle\Controller;


use RuchJow\AppBundle\Entity\JowEvent;
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
class JowEventsController extends ModelController
{


    /**
     * @return Response
     *
     * @Route("/cif/jowEvents/list", name="backend_cif_jow_events_list", options={"expose": true}, condition="request.isXmlHttpRequest()")
     */
    public function listAction()
    {
        $repo = $this->getDoctrine()->getRepository('RuchJowAppBundle:JowEvent');

        $jowEvents = $repo->findAll();
        $ret = array();
        foreach ($jowEvents as $event) {
            $ret[] = $this->jowEventToArray($event);
        }

        return $this->createJsonResponse(array(
            'status' => 'success',
            'data'   => $ret
        ));
    }

    /**
     * @return Response
     *
     * @Route("/cif/jowEvents/update", name="backend_cif_jow_events_update", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function updateAction()
    {
        $em = $this->getDoctrine()->getManager();

        $error = $this->validateRequestJson(
            array(
                'type'     => 'array',
                'children' => array(
                    'id'    => array(
                        'type' => 'entityId',
                        'entity' => 'RuchJowAppBundle:JowEvent',
                        'optional' => true,
                    ),
                    'address' => array('type' => 'string',),
                    'date' => array('type' => 'date',),
                    'venue' => array('type' => 'string',),
                    'title' => array('type' => 'string',),
                    'link' => array('type' => 'string',),
                    'communeId' => array('type' => 'entityId', 'entity' => 'RuchJowTerritorialUnitsBundle:Commune'),
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
            $repo = $em->getRepository('RuchJowAppBundle:JowEvent');

            $event = $repo->find($data['id']);

            if (!$event) {
                return $this->createJsonErrorResponse(array(
                    'status' => 'error',
                    'message' => 'Jow Event could not be found',
                ), 404);
            }
        } else {
            $event = new JowEvent();
        }

        $communeRepo = $em->getRepository('RuchJowTerritorialUnitsBundle:Commune');
        $commune = $communeRepo->find($data['communeId']);

        if (!$commune) {
            return $this->createJsonErrorResponse(array(
                'status' => 'error',
                'message' => 'Commune could not be found',
            ), 400);
        }

        $event->setAddress($data['address'])
            ->setDate(new \DateTime($data['date']))
            ->setVenue($data['venue'])
            ->setTitle($data['title'])
            ->setLink($data['link'])
            ->setCommune($commune);

        $em->persist($event);
        $em->flush();

        return $this->createJsonResponse(array(
            'status' => 'success',
            'data'   => $this->jowEventToArray($event),
        ));
    }

    /**
     * @return Response
     *
     * @Route("/cif/jowEvents/remove", name="backend_cif_jow_events_remove", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function removeAction()
    {
        $em = $this->getDoctrine()->getManager();

        $error = $this->validateRequestJson(
            array(
                'type' => 'entityId',
                'entity' => 'RuchJowAppBundle:JowEvent',
                'optional' => true,
            ),
            $id
        );

        $repo = $em->getRepository('RuchJowAppBundle:JowEvent');
        $event = $repo->find($id);
        if (!$event) {
            return $this->createJsonErrorResponse(array(
                'status' => 'error',
                'message' => 'Jow Event could not be found',
            ), 404);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($event);
        $em->flush();
    }

    /**
     * @param JowEvent $event
     *
     * @return array
     */
    protected function jowEventToArray(JowEvent $event)
    {
        $id = $event->getId();
        $entry = array(
            'id' => $id,
            'address' => $event->getAddress(),
            'date' => $event->getDate()->format('c'),
            'venue' => $event->getVenue(),
            'title' => $event->getTitle(),
            'link' => $event->getLink(),
            'commune' => null
        );

        if ($commune = $event->getCommune()) {
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
