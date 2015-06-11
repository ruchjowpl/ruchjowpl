<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 12/12/14
 * Time: 1:52 PM
 */

namespace RuchJow\MailPoolBundle\Mailer;


class SendError {

    const INTERNAL_MAILER_ERROR = -1;
    const STD_MAILER_ERROR      = 10;

    /**
     * @var int
     */
    protected $code;

    /**
     * @var string
     */
    protected $message;

    public function __construct($code, $message) {
        $this->code = $code;
        $this->mesage = $message;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

}