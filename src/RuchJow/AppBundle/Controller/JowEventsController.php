<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 26/07/15
 * Time: 11:48
 */

namespace RuchJow\AppBundle\Controller;


use RuchJow\AppBundle\Entity\JowEvent;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;


/**
 * Class jowEventsController
 *
 * @package RuchJow\AppBundle\Controller
 *
 * @Route("/jow_events")
 */
class JowEventsController extends ModelController
{
    /**
     * @return Response
     *
     * @Route("/cif/list/{limit}/{offset}", name="app_cif_jow_events_list",
     *     defaults={"limit": 0, "offset": 0},
     *     requirements={"limit": "\d+", "offset": "\d+"},
     *     options={"expose": true})
     * @Method("GET")
     */
    public function listAction($limit, $offset)
    {
        $repo = $this->getDoctrine()->getRepository('RuchJowAppBundle:JowEvent');

        $limit  = (int) $limit ?: null;
        $offset = (int) $offset ?: null;

        $jowEvents = $repo->findIncoming($limit, $offset);

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
     * @param JowEvent $event
     *
     * @return array
     */
    protected function jowEventToArray(JowEvent $event)
    {
        $id    = $event->getId();
        $entry = array(
            'id'      => $id,
            'address' => $event->getAddress(),
            'date'    => $event->getDate()->format('c'),
            'venue'   => $event->getVenue(),
            'title'   => $event->getTitle(),
            'link'    => $event->getLink(),
            'commune' => null
        );

        if ($commune = $event->getCommune()) {
            $entry['commune'] = array(
                'id'       => $commune->getId(),
                'name'     => $commune->getName(),
                'district' => $commune->getDistrict()->getName(),
                'region'   => $commune->getDistrict()->getRegion()->getName(),
            );
        }

        return $entry;
    }
}