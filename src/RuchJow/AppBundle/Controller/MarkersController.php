<?php

namespace RuchJow\AppBundle\Controller;

use RuchJow\AppBundle\Entity\ReferendumPoint;
use RuchJow\AppBundle\Entity\ReferendumPointRepository;
use RuchJow\LocalGovBundle\Entity\Support;
use RuchJow\LocalGovBundle\Entity\SupportRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package RuchJow\TerritorialUnitsBundle\Controller
 *
 * @Route("/markers")
 */
class MarkersController extends ModelController
{
    /**
     * @return Response
     *
     * @Route("/cif/all", name="markers_ajax_all", options={"expose": true}, condition="request.isXmlHttpRequest()")
     * @Method("POST")
     */
    public function getAll()
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

        $retArray = array();

        /** @var SupportRepository $localGovRepo */
        $localGovRepo = $this->getDoctrine()->getRepository('RuchJowLocalGovBundle:Support');
        /** @var Support[] $localGovs */
        $localGovs = $localGovRepo->findByTerritorialUnitLevel($data['type'], isset($data['id']) ? $data['id'] : null);

        foreach ($localGovs as $localGov) {
            $retArray[] = array(
                'type'        => 'support',
                'lat'         => $localGov->getLat(),
                'lng'         => $localGov->getLng(),
                'title'       => $localGov->getTitle(),
                'subtitle'    => $localGov->getSubtitle(),
                'description' => $localGov->getDescription(),
                'link'        => $localGov->getLink(),
                'linkTitle'   => $localGov->getLinkTitle(),
            );
        }

        /** @var ReferendumPointRepository $referendumPointRepo */
        $referendumPointRepo = $this->getDoctrine()->getRepository('RuchJowAppBundle:ReferendumPoint');
        /** @var ReferendumPoint[] $referendumPoints */
        $referendumPoints = $referendumPointRepo->findByTerritorialUnitLevel($data['type'], isset($data['id']) ? $data['id'] : null);

        foreach ($referendumPoints as $referendumPoint) {
            $retArray[] = array(
                'type'        => 'referendum_point',
                'lat'         => $referendumPoint->getLat(),
                'lng'         => $referendumPoint->getLng(),
                'title'       => $referendumPoint->getTitle(),
                'subtitle'    => $referendumPoint->getSubtitle(),
                'description' => $referendumPoint->getDescription(),
            );
        }

        return $this->createJsonResponse(array(
            'status' => 'success',
            'data'   => $retArray
        ));
    }

}
