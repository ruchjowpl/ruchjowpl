<?php

namespace RuchJow\LocalGovBundle\Controller;


use RuchJow\LocalGovBundle\Entity\SupportRepository;
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
     * @Route("/ajax/support", name="local_gov_ajax_support", options={"expose": true})
     * @Method("POST")
     */
    public function getSupport()
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

        /** @var SupportRepository $repo */
        $repo = $this->getDoctrine()->getRepository('RuchJowLocalGovBundle:Support');

        $supports = $repo->findByTerritorialUnitLevel($data['type'], isset($data['id']) ? $data['id'] : null);

        $retArray = array();
        foreach ($supports as $support) {
            $retArray[] = array(
                'lat'         => $support->getLat(),
                'lng'         => $support->getLng(),
                'title'       => $support->getTitle(),
                'subtitle'    => $support->getSubtitle(),
                'description' => $support->getDescription(),
                'link'        => $support->getLink(),
                'linkTitle'   => $support->getLinkTitle(),
            );
        }

        return $this->createJsonResponse($retArray);
    }

}
