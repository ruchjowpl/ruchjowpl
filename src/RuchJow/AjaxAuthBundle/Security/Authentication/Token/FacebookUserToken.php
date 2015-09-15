<?php

namespace RuchJow\AjaxAuthBundle\Security\Authentication\Token;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class FacebookUserToken extends AbstractToken
{
//    public $created;
//    public $digest;
//    public $nonce;

    public $signedRequest;

    public function __construct(array $roles = array())
    {
        parent::__construct($roles);

        // If the user has roles, consider it authenticated
        $this->setAuthenticated(count($roles) > 0);
    }

    public function getCredentials()
    {
        return '';
    }
}