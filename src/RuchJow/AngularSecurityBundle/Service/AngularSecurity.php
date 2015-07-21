<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 19/07/15
 * Time: 17:52
 */

namespace RuchJow\AngularSecurityBundle\Service;


use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfTokenManagerAdapter;
use Symfony\Component\HttpFoundation\RequestStack;

class AngularSecurity
{
    protected $csrfManager;

    protected $requestStack;

    protected $cookieName;

    protected $jsonName;


    public function __construct(CsrfTokenManagerAdapter $csrfManager, RequestStack $requestStack, $cookieName, $jsonName)
    {
        $this->csrfManager = $csrfManager;
        $this->requestStack = $requestStack;
        $this->cookieName = $cookieName;
        $this->jsonName = $jsonName;
    }

    public function validateTokenInJsonRequest($removeToken = true)
    {
        $request = $this->requestStack->getCurrentRequest();
        $content = $request->getContent();

        if (empty($content)) {
            return false;
        }

        $data = json_decode($content, true);

        if (!is_array($data) || !isset($data[$this->jsonName])) {
            return false;
        }

        $expectedToken = $this->generateToken();


        if ($data[$this->jsonName] !== $expectedToken) {
            return false;
        }

        unset($data[$this->jsonName]);

        return true;
    }

    public function generateToken()
    {
        return $this->csrfManager->generateCsrfToken('angular');
    }
}