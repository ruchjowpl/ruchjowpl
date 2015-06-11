<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 12/12/14
 * Time: 12:10 PM
 */

namespace RuchJow\MailPoolBundle\Mailer;


use RuchJow\MailPoolBundle\Entity\PoolElement;

interface MailerInterface {

    /**
     * @param array     $element
     * @param SendError $error
     *
     * @return mixed
     */
    public function send($element, SendError &$error = null);

    /**
     * @return string
     */
    public function getName();

}