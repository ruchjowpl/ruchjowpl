<?php

namespace RuchJow\AppBundle\Controller;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FrontendController
 *
 * @package RuchJow\AppBundle\Controller
 *
 * @Route("/")
 */
class FrontendController extends ModelController
{
    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/", name="frontend_homepage")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $referendumDate = new \DateTime('2015-09-06');
        $today = new \DateTime();

        $interval = $referendumDate->diff($today);
        $daysToReferendum=$interval->format('%a');

        // $_GET parameters
        if ($request->query->count()) {
            if ($url = $request->query->get('url')) {

                return $this->redirect($this->generateUrl('frontend_homepage') . '#' . $url, 301);
            };
        }

        return array('daysToReferendum'=>$daysToReferendum);
    }
}
