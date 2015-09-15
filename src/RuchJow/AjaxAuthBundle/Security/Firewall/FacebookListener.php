<?php

namespace RuchJow\AjaxAuthBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use RuchJow\AjaxAuthBundle\Security\Authentication\Token\FacebookUserToken;

class FacebookListener implements ListenerInterface
{
    protected $tokenStorage;
    protected $authenticationManager;
    protected $loginPath;

    public function __construct(TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, $loginPath)
    {
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->loginPath = $loginPath;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if ($request->getPathInfo() !== $this->loginPath) {
            return;
        }

        $xhr = $request->isXmlHttpRequest();

        if ($request->query->has('signed_request')) {

            $token                = new FacebookUserToken();
            $token->signedRequest = $request->query->get('signed_request');

            try {
                $authToken = $this->authenticationManager->authenticate($token);
                $this->tokenStorage->setToken($authToken);

                $result   = array('success' => true);
                $response = new Response(json_encode($result));
                if ($xhr) {
                    $response->headers->set('Content-Type', 'application/json');
                }

                $event->setResponse($response);

                return;
            } catch (AuthenticationException $failed) {
                // ... you might log something here

                // To deny the authentication clear the token. This will redirect to the login page.
                // Make sure to only clear your token, not those of other authentication listeners.
                // $token = $this->tokenStorage->getToken();
                // if ($token instanceof WsseUserToken && $this->providerKey === $token->getProviderKey()) {
                //     $this->tokenStorage->setToken(null);
                // }
                // return;
            }
        }

        // By default deny authorization
        $result   = array(
            'error' => 'Login failed',
        );
        $response = new Response(json_encode($result));
        $response->setStatusCode(Response::HTTP_FORBIDDEN);
        if ($xhr) {
            $response->headers->set('Content-Type', 'application/json');
        }
        $event->setResponse($response);
    }
}