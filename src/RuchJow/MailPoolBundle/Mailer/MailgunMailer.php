<?php
/**
 * Created by PhpStorm.
 * User: grest
 * Date: 12/12/14
 * Time: 1:03 PM
 */

namespace RuchJow\MailPoolBundle\Mailer;


use RuchJow\MailPoolBundle\Entity\PoolElement;
use Mailgun\Mailgun;

class MailgunMailer implements MailerInterface
{

    /**
     * @var Mailgun
     */
    protected $mailgun;

    /**
     * @var string
     */
    protected $domain;

    public function __construct($apiKey, $domain)
    {
        $this->mailgun = new Mailgun($apiKey);
        $this->domain  = $domain;
    }

    /**
     * @param array     $data
     * @param SendError $error
     *
     * @return bool
     * @throws \Mailgun\Messages\Exceptions\MissingRequiredMIMEParameters
     */
    public function send($data, SendError &$error = null)
    {
        $domain = $this->domain ? $this->domain : substr(strrchr($data['from'], '@'), 1);
        $params = array(
            'from'    => $data['fromName'] . ' <' . $data['from'] . '>',
            'to'      => $data['to'],
            'subject' => $data['subject'],
        );

        if (isset($data['html'])) {
            $params['html'] = $data['html'];
        }

        if (isset($data['text'])) {
            $params['text'] = $data['text'];
        }

        try {
            $result = $this->mailgun->sendMessage(
                $domain,
                $params
            );
        } catch (\Exception $e) {
            $error = new SendError(SendError::INTERNAL_MAILER_ERROR, $e->getMessage());

            return false;
        }

        if ($result->http_response_code !== 200) {
            $error = new SendError(SendError::STD_MAILER_ERROR, serialize($result->http_response_body));

            return false;
        }

        return true;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'Mailgun';
    }
}