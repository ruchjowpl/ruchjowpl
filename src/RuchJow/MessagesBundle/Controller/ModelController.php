<?php

namespace RuchJow\MessagesBundle\Controller;

use RuchJow\MessagesBundle\Entity\Manager;
use RuchJow\PageFoundationBundle\Controller\ModelController as PageFoundationModelController;

/**
 * Class ModelController - provides basic helper functions.
 *
 * @package RuchJow\MessagesBundle\Controller
 */
class ModelController extends PageFoundationModelController
{
    /**
     * @return Manager
     */
    public function getMessagesManager()
    {
        return $this->get('ruch_jow_messages.manager');
    }
}