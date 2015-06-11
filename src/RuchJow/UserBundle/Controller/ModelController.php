<?php

namespace RuchJow\UserBundle\Controller;

use FOS\UserBundle\Util\TokenGenerator;
use RuchJow\PageFoundationBundle\Controller\ModelController as PageFoundationModelController;
use RuchJow\PointsBundle\Services\PointsManager;
use RuchJow\SocialLinksBundle\Entity\SocialLinkManager;
//use RuchJow\UserBundle\Entity\UserManager;
//use RuchJow\UserBundle\Entity\UserRepository;

/**
 * Class ModelController - provides basic helper functions.
 *
 * @package RuchJow\UserBundle\Controller
 */
class ModelController extends PageFoundationModelController
{

//    /**
//     * @return UserRepository
//     */
//    public function getUserRepository()
//    {
//        return $this->get('ruch_jow_user.user_repository');
//    }

//    /**
//     * @return UserManager
//     */
//    public function getUserManager()
//    {
//        return $this->get('fos_user.user_manager');
//    }

    /**
     * @return SocialLinkManager
     */
    public function getSocialLinksManager()
    {
        return $this->get('ruch_jow_social_links.social_link_manager');
    }

    /**
     * @return PointsManager
     */
    public function getPointsManager()
    {
        return $this->get('ruch_jow_points.points_manager');
    }

    /**
     * @return TokenGenerator
     */
    public function getTokenGenerator()
    {
        return $this->get('fos_user.util.token_generator');
    }
}