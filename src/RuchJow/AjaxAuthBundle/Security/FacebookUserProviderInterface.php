<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 12/09/15
 * Time: 22:35
 */

namespace RuchJow\AjaxAuthBundle\Security;


use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

interface FacebookUserProviderInterface extends UserProviderInterface
{
    /**
     * @param $signedRequest
     *
     * @return UserInterface
     */
    public function loadUserBySignedRequest($signedRequest);
}