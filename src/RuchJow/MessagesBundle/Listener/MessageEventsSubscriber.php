<?php

namespace RuchJow\MessagesBundle\Listener;

use Doctrine\ORM\EntityManager;
use RuchJow\MailPoolBundle\Service\MailPool;
use RuchJow\MessagesBundle\Entity\Folder;
use RuchJow\MessagesBundle\Entity\Manager;
use RuchJow\MessagesBundle\Entity\Message;
use RuchJow\MessagesBundle\Entity\Thread;
use RuchJow\MessagesBundle\Event\MessageChangeEvent;
use RuchJow\MessagesBundle\MessageEvents;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;


class MessageEventsSubscriber implements EventSubscriberInterface
{

    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MailPool
     */
    protected $mailPool;

    /**
     * @var EngineInterface
     */
    protected $templateEngine;


    protected $emailParams;
    protected $emailTemplates;

    public function __construct(Manager $manager, EntityManager $entityManager, MailPool $ruchJowMailPool, EngineInterface $templateEngine, $emailParams, $emailTemplates)
    {
        $this->manager       = $manager;
        $this->entityManager = $entityManager;

        $this->templateEngine = $templateEngine;

        $this->mailPool = $ruchJowMailPool;

        $this->emailParams = $emailParams;
        $this->emailTemplates = $emailTemplates;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     *
     * @return array The event names to listen to
     *
     * @api
     */
    public static function getSubscribedEvents()
    {
        return array(
            MessageEvents::MESSAGE_CHANGED => array('onMessageChanged'),
            MessageEvents::MESSAGE_REMOVED => array('onMessageChanged'),
        );
    }

    /**
     * @param MessageChangeEvent $messageEvent
     */
    public function onMessageChanged(MessageChangeEvent $messageEvent)
    {
        $message = $messageEvent->getMessage();

        if ($messageEvent->hasChangedField('thread')) {
            /** @var Thread $oldThread */
            if ($oldThread = $messageEvent->getOldValue('thread')) {
                $this->manager->updateThread($oldThread, false);
            }

            if ($message) {
                if ($thread = $message->getThread()) {
                    $this->manager->updateThread($thread, false);
                }
            }
        };

        if ($messageEvent->hasChangedField('folder')) {
            /** @var Folder $oldFolder */
            if ($oldFolder = $messageEvent->getOldValue('folder')) {
                $this->manager->updateFolder($oldFolder, false);
            }
        }

        if (
            $messageEvent->hasChangedField('isRead')
            || $messageEvent->hasChangedField('folder')
        ) {
            if ($message) {
                if ($folder = $message->getFolder()) {
                    $this->manager->updateFolder($folder, false);
                }
            }
        };

        if (
            $messageEvent->hasChangedField('id')
            && $message
            && $message->getFolder()
            && $message->getFolder()->getName() === Folder::FOLDER_INBOX
        ) {
            $this->sendMail($message);
        }

    }

    protected function sendMail(Message $message) {

        $from = $this->emailParams['from'];
        $fromName = $this->emailParams['from_name'];


        $templateParams = array(
            'message' => $message,
        );
        $subject = $this->templateEngine->render(
            $this->emailTemplates['subject'], // template
            $templateParams
        );
        $body = $this->templateEngine->render(
            $this->emailTemplates['body'], // template
            $templateParams
        );

        $this->mailPool->sendMail(
            $message->getOwner()->getEmail(),
            $subject,
            $body,
            null,
            true,
            $from,
            $fromName
        );
    }
}