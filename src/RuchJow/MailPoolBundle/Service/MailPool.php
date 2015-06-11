<?php

namespace RuchJow\MailPoolBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use RuchJow\MailPoolBundle\Entity\PoolElement;
use RuchJow\MailPoolBundle\Mailer\MailerInterface;
use RuchJow\MailPoolBundle\Mailer\SendError;

class MailPool
{
    protected $entityManager;

    /**
     * @var MailerInterface
     */
    protected $primaryMailer;

    protected $from;
    protected $fromName;
    protected $replyTo;
    protected $deliveryAddress;
    protected $disableDelivery;

    public function __construct(Registry $doctrine, MailerInterface $primaryMailer, /*QbitzFreshMail $freshMailService,*/ $from, $fromName, $replyTo, $deliveryAddress, $disableDelivery)
    {
        $this->entityManager = $doctrine->getManager();

        $this->primaryMailer = $primaryMailer;

        $this->from             = $from;
        $this->fromName         = $fromName;
        $this->replyTo          = $replyTo;
        $this->deliveryAddress  = $deliveryAddress;
        $this->disableDelivery  = $disableDelivery;
    }

    public function sendMail($subscribers, $subject, $content, $tag = null, $isHtml = true, $from = null, $fromName = null, $replyTo = null, &$errors = null)
    {
        if (!is_array($subscribers)) {
            $subscribers = array($subscribers);
        }

        $errors = array();

        foreach ($subscribers as $subscriber) {

            $poolElem = new PoolElement();

            $poolElem
                ->setCreatedAt(new \DateTime())
                ->setSubscriber($subscriber)
                ->setSubject($subject)
                ->setContent($content)
                ->setHtmlContent($isHtml)
                ->setFrom($from)
                ->setFromName($fromName)
                ->setReplyTo($replyTo ? $replyTo : $from)
                ->setTag($tag);

            $this->entityManager->persist($poolElem);

            if ($errorCode = $this->sendPoolElement($poolElem)) {
                $errors[$subscriber] = $errorCode;
            };

        }

        $this->entityManager->flush();

        return count($errors);
    }


    protected function sendPoolElement(PoolElement &$elem, $flush = true, $userPrimaryMailer = true)
    {

        // Choose Mailer
        // FIXME: Add secondaryMailer.
        $mailer = $userPrimaryMailer ? $this->primaryMailer : $this->primaryMailer;
        $elem->setLastMailer($mailer->getName());

        // Prepare send data
        $data = array(
            'to'       => $this->deliveryAddress ? $this->deliveryAddress : $elem->getSubscriber(),
            'subject'  => $elem->getSubject(),
            'from'     => $elem->getFrom() ? $elem->getFrom() : $this->from,
            'fromName' => $elem->getFromName() ? $elem->getFromName() : $this->fromName,
        );

        // Content
        if ($elem->isHtmlContent()) {
            $data['html'] = $elem->getContent();
        } else {
            $data['text'] = $elem->getContent();
        }

//        // ReplyTo
//        $tmp = $elem->getReplyTo() ? $elem->getReplyTo() : $this->replyTo;
//        if ($tmp) {
//            $data['replyTo'] = $tmp;
//        }
//
//        // Tag
//        if ($elem->getTag()) {
//            $data['tag'] = $elem->getTag();
//        }

        if (!$this->disableDelivery) {
            /** @var SendError $error */
            if (!$mailer->send($data, $error)) {
                $this->setError($elem, $error->getCode(), $error->getMessage(), $flush);

                return $error->getCode();
            };


//            try {
//                $this->freshMailService->doRequest(
//                    'mail',
//                    $data
//                );
//            } catch (RestException $e) {
//                $this->setError($elem, $e->getCode(), $e->getMessage());
//
//                return $e->getCode();
//            } catch (\Exception $e) {
//                $this->setError($elem, PoolElement::InternalMailPoolError, PoolElement::InternalMailPoolErrorMessage, $flush);
//
//                return PoolElement::InternalMailPoolError;
//            }
        }

        $elem->setSentAt(new \DateTime());
        $this->entityManager->persist($elem);

        if ($flush) {
            $this->entityManager->flush($elem);
        }

        return 0;
    }


    public function setError(PoolElement $poolElement, $code, $message, $flush = true)
    {
        $poolElement
            ->incFailedSendAttempts()
            ->setNextSendAttemptAfter(
                $this->getNextAttempt($poolElement->getFailedSendAttempts())
            );

        $poolElement->setLastError($code);
        $poolElement->setLastErrorMessage($message);

        $poolElement->setLastErrorAt(new \DateTime());

        $this->entityManager->persist($poolElement);

        if ($flush) {
            $this->entityManager->flush();
        }
    }

    /**
     * @param $failedAttempts
     *
     * @return \DateTime|null
     */
    protected function getNextAttempt($failedAttempts)
    {
        if ($failedAttempts <= 5) {
            $dateInterval = new \DateInterval('PT2M');
        } elseif ($failedAttempts <= 7) {
            $dateInterval = new \DateInterval('PT5M');
        } elseif ($failedAttempts <= 11) {
            $dateInterval = new \DateInterval('PT10M');
        } elseif ($failedAttempts <= 14) {
            $dateInterval = new \DateInterval('PT20M');
        } elseif ($failedAttempts <= 24) {
            $dateInterval = new \DateInterval('PT1H');
        } elseif ($failedAttempts <= 31) {
            $dateInterval = new \DateInterval('P1D');
        } else {
            return null;
        }

        $date = new \DateTime();

        return $date->add($dateInterval);
    }


}