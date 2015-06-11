<?php

namespace RuchJow\AjaxAuthBundle\Listener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * Class AjaxAuthenticationListener
 *
 * @package RuchJow\AjaxAuthBundle\Listener
 */
class AjaxAuthenticationListener
{

    private $securityContext;

    /**
     * @param SecurityContext $securityContext
     */
    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * Handles security related exceptions.
     *
     * @param GetResponseForExceptionEvent $event An GetResponseForExceptionEvent instance
     */
    public function onCoreException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();
        $request   = $event->getRequest();

        if ($request->isXmlHttpRequest()) {
            if ($exception instanceof AccessDeniedException || $exception instanceof AuthenticationException) {
                if (!$this->securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
                    $responseData = array('status' => 401, 'msg' => 'User is not authenticated.');
                } else {
                    $responseData = array('status' => 403, 'msg' => 'Access denied.');
                }
                $response = new JsonResponse();
                $response->setData($responseData);
                $response->setStatusCode($responseData['status']);
                $event->setResponse($response);
            }
        }
    }
}