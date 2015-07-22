<?php

namespace RuchJow\FeedBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package RuchJow\FeedBundle\Controller
 *
 * @Route("/")
 */
class DefaultController extends ModelController
{
    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/announcements", name="feed_announcements", options={"expose"=true}, condition="request.isXmlHttpRequest()")
     */
    public function announcementsAction(Request $request)
    {
        $feedsConfig = $this->getParameter('ruch_jow_feed.feeds');

        return $this->performRequest(
            $feedsConfig['announcements']['url'],
            $feedsConfig['announcements']['timeout']
        );
    }
}
