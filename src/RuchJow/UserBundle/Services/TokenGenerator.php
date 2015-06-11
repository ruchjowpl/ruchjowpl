<?php

namespace RuchJow\UserBundle\Services;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Util\SecureRandom;

class TokenGenerator
{
    protected $container;

    protected $tokensParam;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function generateToken($len)
    {
        if (!is_string($len)) {
            $len = $this->getTokenLength($len);
            if (!$len) {
                throw new \Exception('Parameter ' . $len . 'is not defined or its value is incorrect.');
            }
        }

        $generator = new SecureRandom();
        $random    = $generator->nextBytes(10);

        return rtrim(strtr(base64_encode($random), '+/', '-_'), '=');
    }

    protected function getTokenLength($name)
    {
        if (!$this->tokensParam) {
            $this->tokensParam = $this->container->getParameter('ruch_jow_user.token_generator.tokens');
        }

        return isset($this->tokensParam[$name])
            ? $this->tokensParam[$name]
            : null;
    }
}
