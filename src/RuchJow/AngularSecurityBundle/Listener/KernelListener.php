<?php

namespace RuchJow\AngularSecurityBundle\Listener;

use RuchJow\AngularSecurityBundle\Service\AngularSecurity;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;

/**
 * Class KernelListener
 *
 * @package RuchJow\AngularSecurityBundle\Listener
 */
class KernelListener
{

    protected $angularSecurity;

    protected $xsrfCookieName;

    protected $xsrfHeaderName;

    public function __construct(AngularSecurity $angularSecurity, $xsrfCookieName, $xsrfHeaderName)
    {
        $this->angularSecurity = $angularSecurity;
        $this->xsrfCookieName = $xsrfCookieName;
        $this->xsrfHeaderName = $xsrfHeaderName;
    }

    /**
     * @param FilterResponseEvent $event
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();

        $oldToken = $request->cookies->get($this->xsrfCookieName);
        $newToken = $this->angularSecurity->generateToken();

        if (!$oldToken || $oldToken !== $newToken) {
            $cookie = new Cookie($this->xsrfCookieName, $newToken, 0, '/', null, false, false);
            $event->getResponse()->headers->setCookie($cookie);
        }
    }

    /**
     * @param GetResponseEvent $event
     *
     * @return Response
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->isXmlHttpRequest()) {
            if ($request->headers->get($this->xsrfHeaderName) !== $this->angularSecurity->generateToken()) {
                $response = new Response('Token expired/invalid', 400, array($this->xsrfHeaderName => 'invalid'));
                $event->setResponse($response);
            }
        }
    }
}