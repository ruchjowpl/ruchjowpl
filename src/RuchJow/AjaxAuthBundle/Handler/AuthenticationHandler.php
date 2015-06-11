<?php

namespace RuchJow\AjaxAuthBundle\Handler;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\HttpUtils;
use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;

/**
 * Class AuthenticationHandler
 *
 * @package RuchJow\AjaxAuthBundle\Handler
 */
class AuthenticationHandler
    implements AuthenticationSuccessHandlerInterface,
    AuthenticationFailureHandlerInterface,
    LogoutSuccessHandlerInterface
{
    private $httpUtils;
    private $loginTarget;
    private $logoutTarget;

    /**
     * @param HttpUtils $httpUtils
     * @param string    $loginTarget
     * @param string    $loginFailureTarget
     * @param string    $logoutTarget
     */
    public function __construct(HttpUtils $httpUtils, $loginTarget = '/', $loginFailureTarget = "/login", $logoutTarget = '/')
    {
        $this->httpUtils          = $httpUtils;
        $this->loginTarget        = $loginTarget;
        $this->loginFailureTarget = $loginFailureTarget;
        $this->logoutTarget       = $logoutTarget;
    }

    /**
     * @param Request        $request
     * @param TokenInterface $token
     *
     * @return RedirectResponse|Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token)
    {
        if ($request->isXmlHttpRequest()) {
            // Handle XHR here
            $result   = array('success' => true);
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            // If the user tried to access a protected resource and was forced to login
            // redirect him back to that resource
            if ($targetPath = $request->getSession()->get('_security.main.target_path')) {
                $url = $targetPath;
            } else {
                // Otherwise, redirect him to wherever you want
                $url = $this->loginTarget;
            }

            return $this->httpUtils->createRedirectResponse($request, $url);
        }
    }

    /**
     * @param Request                 $request
     * @param AuthenticationException $exception
     *
     * @return RedirectResponse|Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        if ($request->isXmlHttpRequest()) {
            $result   = array(
                'error' => $exception->getMessage(),
            );
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            return $this->httpUtils->createRedirectResponse($request, $this->loginFailureTarget);
        }
    }

    /**
     * Creates a Response object to send upon a successful logout.
     *
     * @param Request $request
     *
     * @return Response never null
     */
    public function onLogoutSuccess(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            // Handle XHR here
            $result   = array('success' => true);
            $response = new Response(json_encode($result));
            $response->headers->set('Content-Type', 'application/json');

            return $response;
        } else {
            return $this->httpUtils->createRedirectResponse($request, $this->logoutTarget);
        }
    }
}

