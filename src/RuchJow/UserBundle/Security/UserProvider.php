<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 12/09/15
 * Time: 18:35
 */

namespace RuchJow\UserBundle\Security;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Security\UserProvider as FOSUserProvider;
use RuchJow\AjaxAuthBundle\Security\FacebookUserProviderInterface;
use RuchJow\FacebookBundle\Services\Facebook;

class UserProvider extends FOSUserProvider implements FacebookUserProviderInterface
{
    /**
     * @var \Facebook\Facebook
     */
    protected $facebook;

    /**
     * Constructor.
     *
     * @param UserManagerInterface $userManager
     */
    public function __construct(UserManagerInterface $userManager, Facebook $facebook)
    {
        parent::__construct($userManager);

        $this->facebook = $facebook->getFacebook();
    }

    public function loadUserBySignedRequest($signedRequest)
    {
        $helper = $this->facebook->getJavaScriptHelper();
        $helper->instantiateSignedRequest($signedRequest);

        try {
            $userId = $helper->getUserId();
        } catch(FacebookResponseException $e) {
            // When Graph returns an error
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(FacebookSDKException $e) {
            // When validation fails or other local issues
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }

        $user = $this->userManager->findUserBy(array('facebookId' => $userId));
        return $user;
    }

}