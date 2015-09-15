<?php

namespace RuchJow\AjaxAuthBundle\Security\Authentication\Provider;

use RuchJow\AjaxAuthBundle\Security\FacebookUserProviderInterface;
use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use RuchJow\AjaxAuthBundle\Security\Authentication\Token\FacebookUserToken;

class FacebookProvider implements AuthenticationProviderInterface
{
    private $userProvider;

    public function __construct(FacebookUserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    /**
     * @param FacebookUserToken|TokenInterface $token
     *
     * @return FacebookUserToken
     *
     * @throws \Exception
     */
    public function authenticate(TokenInterface $token)
    {
        $user = $this->userProvider->loadUserBySignedRequest($token->signedRequest);

        if ($user) {
            $authenticatedToken = new FacebookUserToken($user->getRoles());
            $authenticatedToken->setUser($user);

            return $authenticatedToken;
        }

        throw new AuthenticationException('The Facebook authentication failed.');
    }

    public function supports(TokenInterface $token)
    {
        return $token instanceof FacebookUserToken;
    }
}