<?php

namespace RuchJow\AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class FrontendController
 *
 * @package RuchJow\AppBundle\Controller
 *
 * @Route("/")
 */
class AppController extends ModelController
{
    /**
     * @Route("/parameters.js", name="app_parameters", options={"expose"=true})
     *
     * @return Response
     */
    public function angularParametersAction()
    {
        $response = new Response('', 200, array(
            'Content-Type' => 'text/javascript',
        ));
        $container = $this->container;
        $parameters = array();

        //declare which parameters from container you want to inject into angular app
        $requestedParameters = array(
            'facebook_client_id',
        );

        foreach ($requestedParameters as $parameter) {
            if ($container->hasParameter($parameter)) {
                $parameters[$parameter]=$container->getParameter($parameter);
            }
        }

        return $this->render(
            'RuchJowAppBundle:App:parameters.js.twig',
            array('parameters'=>$parameters),
            $response
        );
    }
}
