<?php

namespace RuchJow\MessagesBundle\Controller;

use Doctrine\ORM\EntityManager;
use RuchJow\MessagesBundle\Entity\Folder;
use RuchJow\MessagesBundle\Entity\Message;
use RuchJow\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DefaultController
 *
 * @package RuchJow\MessagesBundle\Controller
 *
 * @Route("")
 */
class DefaultController extends ModelController
{
    /**
     * @return Response
     *
     * @Route("/ajax/folders", name="msgs_cif_get_folders", options={"expose": true})
     * @Method("GET")
     */
    public function getFoldersAction()
    {

        $manager = $this->getMessagesManager();
        $user    = $this->getUser();

        if (!$user) {
            return $this->createJsonErrorNotGrantedResponse(array(
                'status'  => 'fail',
                'message' => 'Could not find current user.'
            ));
        }

        $folders    = $manager->getFolderRepo()->findByOwner($user);
        $folderList = array(
            Folder::FOLDER_INBOX => array('messages' => 0, 'unread' => 0,),
            Folder::FOLDER_SENT  => array('messages' => 0, 'unread' => 0,),
        );

        foreach ($folders as $folder) {
            $folderList[$folder->getName()] = array(
                'messages' => $folder->getMessageCnt(),
                'unread'   => $folder->getUnreadCnt()
            );
        }

        return $this->createJsonResponse(array(
            'status' => 'success',
            'data'   => $folderList,
        ));
    }

    /**
     * @return Response
     *
     * @Route("/ajax/messages/{folder}/{start}/{cnt}", name="msgs_cif_get_messages", options={"expose": true}, requirements={"folder": "(\#|[a-zA-Z0-9_-])+", "start": "\d+", "cnt": "[1-9]\d*"})
     * @Method("GET")
     */
    public function getMessagesAction($folder, $start, $cnt)
    {
        $start = intval($start);
        $cnt   = intval($cnt);

        // FIXME maximum cnt value must not be hardcoded!!!
        if ($start < 0 || $cnt < 1 || $cnt > 100) {
            return $this->createJsonErrorResponse(array(
                'status'  => 'fail',
                'message' => 'Incorrect parameters.',
            ), 404);
        }

        $manager = $this->getMessagesManager();
        $user    = $this->getUser();

        if (!$user) {
            return $this->createJsonErrorNotGrantedResponse(array(
                'status'  => 'fail',
                'message' => 'Could not find current user.',
            ));
        }

        $messages    = $manager->getMessageRepo()->findByFolderLimited($user, $folder, $start, $cnt);
        $messageList = array();

        foreach ($messages as $message) {
            $messageList[] = array(
                'id'        => $message->getId(),
                'sender'    => $message->getSender()->getUsername(),
                'recipient' => $message->getRecipient()->getUsername(),
                'subject'   => $message->getSubject(),
//                'body'      => $message->getBody(),
                'sentAt'    => $message->getSentAt() ? $message->getSentAt()->format('c') : null,
                'readAt'    => $message->getReadAt() ? $message->getReadAt()->format('c') : null,
                'isRead'    => $message->getIsRead(),
            );
        }

        return $this->createJsonResponse(array(
            'status' => 'success',
            'data'   => $messageList,
        ));
    }

    /**
     * @return Response
     *
     * @Route("/ajax/message/get/{id}", name="msgs_cif_get_message", options={"expose": true}, requirements={"id": "[1-9]\d*"})
     * @Method("GET")
     */
    public function getMessageAction($id)
    {
        $id = intval($id);

        $manager = $this->getMessagesManager();
        $user    = $this->getUser();

        if (!$user) {
            return $this->createJsonErrorNotGrantedResponse(array(
                'status'  => 'fail',
                'message' => 'Could not find current user.',
            ));
        }

        /** @var Message $message */
        $message = $manager->getMessageRepo()->find($id);
        if (!$message || $message->getOwner()->getId() !== $user->getId()) {
            return $this->createJsonErrorResponse(array(
                'status'  => 'fail',
                'message' => 'Could not find message.',
            ), 404);
        }

        $message->setIsRead(true);

        $messageArray = array(
            'id'        => $message->getId(),
            'sender'    => $message->getSender()->getUsername(),
            'recipient' => $message->getRecipient()->getUsername(),
            'subject'   => $message->getSubject(),
            'body'      => $message->getBody(),
            'sentAt'    => $message->getSentAt() ? $message->getSentAt()->format('c') : null,
            'readAt'    => $message->getReadAt() ? $message->getReadAt()->format('c') : null,
            'isRead'    => $message->getIsRead(),
        );

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $em->persist($message);
        $em->flush();

        return $this->createJsonResponse(array(
            'status' => 'success',
            'data' => $messageArray,
        ));
    }

    /**
     * @return Response
     *
     * @Route("/ajax/message/send", name="msgs_cif_send_message", options={"expose": true})
     * @Method("POST")
     */
    public function sendMessageAction()
    {
        $error = $this->validateRequestJson(
            array(
                'type' => 'array',
                'children' => array(
                    'recipient' => array(
                        'type' => 'string',
                        'optional' => false,
                    ),
                    'subject' => array(
                        'type' => 'string',
                        'optional' => false,
                    ),
                    'body' => array(
                        'type' => 'string',
                        'optional' => false,
                    ),
                    'relatedMessageId' => array(
                        'type' => 'entityId',
                        'entity' => 'RuchJowMessagesBundle:Message',
                        'optional' => true
                    )
                )
            ),
            $messageData
        );

        if ($error) {
            return $this->createJsonErrorResponse(array(
                'status' => 'fail',
                'message' => $error['message'],
            ));
        }

        $manager = $this->getMessagesManager();
        $user    = $this->getUser();
        if (!$user) {
            return $this->createJsonErrorNotGrantedResponse(array(
                'status'  => 'fail',
                'message' => 'Could not find current user.',
            ));
        }

        // FIXME max subject size should not be hardcoded
        if (strlen($messageData['subject']) > 255) {
            return $this->createJsonErrorNotGrantedResponse(array(
                'status'  => 'fail',
                'message' => 'Subject is too long.',
            ));
        }

        // FIXME max body size should not be hardcoded
        if (strlen($messageData['body']) > 1000) {
            return $this->createJsonErrorNotGrantedResponse(array(
                'status'  => 'fail',
                'message' => 'Body is too long.',
            ));
        }

        /** @var User $recipient */
        $recipient = $this->getUserManager()->findUserByUsername($messageData['recipient']);
        if (!$recipient) {
            return $this->createJsonErrorNotGrantedResponse(array(
                'status'  => 'fail',
                'message' => 'Recipient not found',
            ));
        }

        if (!$recipient->isEnabled() || !$recipient->isSupports()) {
            return $this->createJsonErrorNotGrantedResponse(array(
                'status'  => 'fail',
                'message' => 'Recipient is disabled',
            ));
        }

        if (isset($messageData['relatedMessageId'])) {
            /** @var Message $message */
            $message = $manager->getMessageRepo()->find($messageData['relatedMessageId']);
            if (!$message || $message->getOwner()->getId() !== $user->getId()) {
                return $this->createJsonErrorResponse(array(
                    'status'  => 'fail',
                    'message' => 'Related message not found',
                ), 404);
            }

            if (
                $message->getSender()->getId() !== $user->getId()
                && $message->getSender()->getId() !== $recipient->getId()
                && $message->getRecipient()->getId() !== $user->getId()
                && $message->getRecipient()->getId() !== $recipient->getId()
            ) {
                return $this->createJsonErrorResponse(array(
                    'status'  => 'fail',
                    'message' => 'Related message is... not related!',
                ), 400);
            }
        }

        $manager->sendMessage(
            $user,
            $recipient,
            $messageData['subject'],
            $messageData['body'],
            isset($message) ? $message : null,
            true
        );

        return $this->createJsonResponse(array(
            'status' => 'success'
        ));
    }
}
