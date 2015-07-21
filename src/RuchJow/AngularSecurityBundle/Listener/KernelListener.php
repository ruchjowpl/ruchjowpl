<?php

namespace RuchJow\AngularSecurityBundle\Listener;

use RuchJow\AngularSecurityBundle\Service\AngularSecurity;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
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

    public function __construct(AngularSecurity $angularSecurity, $xsrfCookieName)
    {
        $this->angularSecurity = $angularSecurity;
        $this->xsrfCookieName = $xsrfCookieName;
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
            $cookie = new Cookie($this->xsrfCookieName, $newToken);
            $event->getResponse()->headers->setCookie($cookie);
        }
    }
}