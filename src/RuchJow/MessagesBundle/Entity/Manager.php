<?php

namespace RuchJow\MessagesBundle\Entity;


use Doctrine\ORM\EntityManager;
use RuchJow\UserBundle\Entity\User;
use Symfony\Component\Security\Core\SecurityContext;

class Manager
{

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SecurityContext
     */
    protected $securityContext;

    /**
     * @var ThreadRepository
     */
    protected $threadRepo;

    /**
     * @var FolderRepository
     */
    protected $folderRepo;

    /**
     * @var MessageRepository
     */
    protected $messageRepo;

    /**
     * @var ThreadsParentRepository
     */
    protected $threadsParentRepo;


    public function __construct(EntityManager $entityManger, SecurityContext $securityContext)
    {
        $this->entityManager   = $entityManger;
        $this->securityContext = $securityContext;
    }

    public function sendMessage(User $sender, User $recipient, $subject, $body, Message $relatedMessage = null, $flush = true)
    {
        $recipientMessage = new Message();
        $senderMessage = new Message;

        // Prepare recipient and sender messages
        // Recipient
        $recipientMessage
            ->setOwner($recipient)
            ->setSender($sender)
            ->setRecipient($recipient)
            ->setSubject($subject)
            ->setBody($body);

        // Sender
        $senderMessage
            ->setOwner($sender)
            ->setSender($sender)
            ->setRecipient($recipient)
            ->setSubject($subject)
            ->setBody($body)
            ->setIsRead(true);

        // Assign messages to correct folders
        // Recipient
        $rFolder = $this->findFolder($recipient, Folder::FOLDER_INBOX);
        if (!$rFolder) {
            $rFolder = $this->createFolder($recipient, Folder::FOLDER_INBOX, false);
        }
        $recipientMessage->setFolder($rFolder);

        // Sender
        $sFolder = $this->findFolder($sender, Folder::FOLDER_SENT);
        if (!$sFolder) {
            $sFolder = $this->createFolder($sender, Folder::FOLDER_SENT, false);
        }
        $senderMessage->setFolder($sFolder);


        // Prepare thread and threadsParent for recipient message.
        if ($relatedMessage) {
            $threadsParent = $relatedMessage->getThread()->getThreadsParent();
            $rThread = $this->findRelatedThread($relatedMessage, $recipient);

            if (!$rThread) {
                $rThread = $this->createThread($recipient, $threadsParent, false);
            }
        } else {
            $rThread = $this->createThread($recipient, null, false);
            $threadsParent = $rThread->getThreadsParent();
        }
        $recipientMessage->setThread($rThread);

        // Prepare thread for sender message.
        $sThread = null;
        if ($relatedMessage) {
            $sThread = $this->findRelatedThread($relatedMessage, $sender);
        }

        if (!$sThread) {
            $sThread = $this->createThread($sender, $threadsParent, false);
        }
        $senderMessage->setThread($sThread);

        // Persist
        $this->entityManager->persist($recipientMessage);
        $this->entityManager->persist($senderMessage);

        // Flush
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findRelatedThread(Message $message, User $user)
    {
        if ($message->getOwner()->getId() === $user->getId()) {
            return $message->getThread();
        }

        return $this->getThreadRepo()
            ->findByParentAndOwner($message->getThread()->getThreadsParent(), $user);
    }

    public function createThread(User $owner, ThreadsParent $parent = null, $flush = true)
    {
        if (!$parent) {
            $parent = new ThreadsParent();
            $this->entityManager->persist($parent);
        }

        $thread = new Thread();
        $thread->setOwner($owner)
            ->setThreadsParent($parent);

        $this->entityManager->persist($thread);

        if ($flush) {
            $this->entityManager->flush();
        }

        return $thread;
    }



    public function updateThread(Thread $thread, $flush = true)
    {
        $repo = $this->getThreadRepo();
        $qb   = $repo->createQueryBuilder('t');
        $qb->join('t.messages', 'm')
            ->select('count(m.id) cnt, max(m.sentAt) lastAt')
            ->where($qb->expr()->eq('t.id', ':id'))
            ->setParameter('id', $thread->getId());

        $ret = $qb->getQuery()->getResult();

        if ($cnt = $ret[0]['cnt']) {
            $thread->setMessageCnt(intval($cnt))
                ->setLastMessageAt($ret[0]['lastAt'] ? new \DateTime($ret[0]['lastAt']) : null);

            // PERSIST
            $this->entityManager->persist($thread);
        } else {

            // REMOVE
            $this->entityManager->remove($thread);
        }

        // FLUSH
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function updateFolder(Folder $folder, $flush = true)
    {
        $repo = $this->getFolderRepo();

        // Get messages count.
        $qb   = $repo->createQueryBuilder('f');
        $qb->join('f.messages', 'm')
            ->select('count(m.id) cnt, max(m.sentAt)')
            ->where($qb->expr()->eq('f.id', ':id'))
            ->setParameter('id', $folder->getId());

        $ret = $qb->getQuery()->getResult();
        $folder->setMessageCnt(intval($ret[0]['cnt']));

        // Get unread messages count.
        $qb = $repo->createQueryBuilder('f');
        $qb->join('f.messages', 'm')
            ->select('count(m.id) cnt')
            ->where($qb->expr()->eq('f.id', ':id'))
            ->setParameter('id', $folder->getId())
            ->andWhere($qb->expr()->eq('m.isRead', ':isRead'))
            ->setParameter('isRead', false);

        $ret = $qb->getQuery()->getResult();
        $folder->setUnreadCnt(intval($ret[0]['cnt']));



        // PERSIST
        $this->entityManager->persist($folder);

        // FLUSH
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function updateThreadsParent(ThreadsParent $threadsParent, $flush = true)
    {
        $repo = $this->getThreadsParentRepo();
        $qb   = $repo->createQueryBuilder('tp');
        $qb->join('tp.threads', 't')
            ->select('count(t.id) cnt')
            ->where($qb->expr()->eq('tp.id', ':id'))
            ->setParameter('id', $threadsParent->getId());

        $ret = $qb->getQuery()->getResult();

        if ($cnt = $ret[0]['cnt']) {
            $threadsParent->setThreadsCnt(intval($cnt));

            // PERSIST
            $this->entityManager->persist($threadsParent);
        } else {

            // REMOVE
            $this->entityManager->remove($threadsParent);
        }

        // FLUSH
        if ($flush) {
            $this->entityManager->flush();
        }
    }


    /**
     * @param User $user
     * @param      $name
     *
     * @return Folder
     */
    public function findFolder(User $user, $name)
    {
        return $this->getFolderRepo()->findByOwnerAndName($user, $name);
    }

    public function createFolder($user, $name, $flush = true)
    {
        $folder = new Folder();
        $folder->setName($name)
            ->setOwner($user);

        $this->entityManager->persist($folder);

        if ($flush) {
            $this->entityManager->flush();
        }

        return $folder;
    }


    /**
     * @return ThreadsParentRepository
     */
    public function getThreadsParentRepo()
    {
        if (!$this->threadsParentRepo) {
            $this->threadsParentRepo = $this->entityManager->getRepository('RuchJowMessagesBundle:ThreadsParent');
        }

        return $this->threadsParentRepo;
    }

    /**
     * @return ThreadRepository
     */
    public function getThreadRepo()
    {
        if (!$this->threadRepo) {
            $this->threadRepo = $this->entityManager->getRepository('RuchJowMessagesBundle:Thread');
        }

        return $this->threadRepo;
    }

    /**
     * @return FolderRepository
     */
    public function getFolderRepo()
    {
        if (!$this->folderRepo) {
            $this->folderRepo = $this->entityManager->getRepository('RuchJowMessagesBundle:Folder');
        }

        return $this->folderRepo;
    }

    public function getMessageRepo()
    {
        if (!$this->messageRepo) {
            $this->messageRepo = $this->entityManager->getRepository('RuchJowMessagesBundle:Message');
        }

        return $this->messageRepo;
    }




}