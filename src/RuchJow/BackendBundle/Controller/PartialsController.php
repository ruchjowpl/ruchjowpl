<?php

namespace RuchJow\BackendBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
//use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

/**
 * Class PartialsController
 *
 * @package RuchJow\BackendBundle\Controller
 *
 * @Route("/partial")
 */
class PartialsController extends Controller
{
    /**
     * Serves angular partials
     *
     * @param string $template
     *
     * @throws NotFoundHttpException
     * @return Response
     *
     * @Route("/{template}", name="backend_partial", options={"expose"=true})
     */
    public function partialAction($template)
    {
        try {
            $content = $this->renderView(
                'RuchJowBackendBundle:Partials:' . $template . '.tpl.html.twig'
            );
        } catch (\Exception $e) {
            throw $this->createNotFoundException();
        }

        return new Response($content);
    }
}
