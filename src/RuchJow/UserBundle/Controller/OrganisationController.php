<?php

namespace RuchJow\UserBundle\Controller;

use RuchJow\UserBundle\Entity\Organisation;
use RuchJow\UserBundle\Entity\OrganisationRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package RuchJow\UserBundle\Controller
 *
 * @Route("/")
 */
class OrganisationController extends ModelController
{
    /**
     * @return Response
     *
     * @Route("/ajax/organisations", name="user_ajax_organisations", options={"expose": true}, condition="request.isXmlHttpRequest()")
     */
    public function findOrganisationsByUrlAction()
    {
        $error = $this->validateRequestJson(
            array('type' => 'string'),
            $data
        );

        if ($error) {
            $this->createJsonErrorResponse($error['message']);
        }

//        if (!preg_match('/^([0-9a-ąćęłńóśźż\.-]+)\.([a-z\.]{2,6})(\/[a-zA-Z0-9\.-_]*)*\/?$/', $data)) {
//            $this->createJsonErrorResponse('Site address format not supported.');
//        }

        /** @var OrganisationRepository $repo */
        $repo = $this->getDoctrine()
            ->getRepository('RuchJowUserBundle:Organisation');

        $organisations = $repo->findByUrlPart($data, 8);

        $retArray = $this->prepareOrganisationsArray($organisations);

        return $this->createJsonResponse($retArray);
    }

    /**
     * @param Organisation[] $organisations
     *
     * @return array
     */
    protected function prepareOrganisationsArray($organisations)
    {
        $retArray = array();
        foreach ($organisations as $organisation) {
            $retArray[] = array(
                'id'   => $organisation->getId(),
                'name' => $organisation->getName(),
                'url'  => $organisation->getUrl(),
            );
        }

        return $retArray;
    }
}
