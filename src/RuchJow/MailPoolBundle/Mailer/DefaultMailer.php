<?php

namespace RuchJow\MailPoolBundle\Mailer;


use RuchJow\MailPoolBundle\Entity\PoolElement;
use Mailgun\Mailgun;

class DefaultMailer implements MailerInterface
{

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var string
     */
    protected $domain;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
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
        $message = \Swift_Message::newInstance()
            ->setSubject($data['subject'])
            ->setFrom(array($data['from'] => $data['fromName']))
            ->setTo($data['to']);

        if (isset($data['html'])) {
            $message->setBody(
                $data['html'],
                'text/html'
            );
        }

        if (isset($data['text'])) {
            $message->setBody(
                $data['text'],
                'text/plain'
            );
        }
        $sent = $this->mailer->send($message, $failedRecipients);

        if (!empty($failedRecipients)) {

            return false;
        }

        return true;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return 'Default';
    }
}