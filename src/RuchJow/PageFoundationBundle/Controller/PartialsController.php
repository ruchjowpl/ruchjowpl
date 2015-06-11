<?php

namespace RuchJow\PageFoundationBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Class PartialsController
 *
 * @package RuchJow\PageFoundationBundle\Controller
 *
 * @Route("/foundation/partial")
 */
class PartialsController extends ModelController
{

    /**
     * Serves angular partials
     *
     * @param string $template
     *
     * @throws NotFoundHttpException
     * @return Response
     *
     * @Route("/{template}", name="foundation_partial", options={"expose"=true})
     */
    public function partialAction($template)
    {
        try {
            $content = $this->renderView(
                'RuchJowPageFoundationBundle:Partials:' . $template . '.tpl.html.twig'
            );
        } catch (\Exception $e) {
            throw $this->createNotFoundException();
        }

        return new Response($content);
    }
}
