<?php

namespace RuchJow\MessagesBundle\Doctrine;


use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use RuchJow\MessagesBundle\Entity\Message;
use RuchJow\MessagesBundle\Event\MessageChangeEvent;
use RuchJow\MessagesBundle\MessageEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class MessageDoctrineSubscriber implements EventSubscriber
{

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var array
     */
    protected $data = array();

    protected $watchedFields = array(
        'id',
        'folder',
        'thread',
        'isRead',
    );

    /**
     * @param EventDispatcherInterface $dispatcher
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return array(
//            'preRemove',
            'postRemove',
//            'prePersist',
            'preUpdate',
            'postPersist',
//            'postUpdate',
            'postFlush',
        );
    }


    public function postRemove(LifecycleEventArgs $args){
        // FIXME Chenge set must be prepared.
        $entity = $args->getObject();
        if ($entity instanceof Message) {
            $data = array(
                'entity'    => null,
                'changeSet' => array()
            );

            $this->data[$entity->getId()] = $data;
        }
    }

//    public function prePersist(LifecycleEventArgs $args)
//    {
//        $entity = $args->getObject();
//        if ($entity instanceof Message) {
//
//            // Dispatch message pre persist event.
//            $this->dispatcher->dispatch(
//                MessageEvents::MESSAGE_PRE_PERSIST,
//                new MessageLifecycleEvent($entity, $args)
//            );
//        }
//    }


    /**
     * @param LifecycleEventArgs|PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Message) {
//            $this->dispatcher->dispatch(
//                MessageEvents::MESSAGE_PRE_UPDATE,
//                new MessageLifecycleEvent($entity, $args)
//            );

            $changeSet = $args->getEntityChangeSet();
            if (!empty($changeSet)) {
                $data = array(
                    'entity'    => $entity,
                    'changeSet' => array(),
                );

                foreach ($this->watchedFields as $fieldName) {
                    if ($args->hasChangedField($fieldName)) {
                        $data['changeSet'][$fieldName] = $args->getOldValue($fieldName);
                    }
                }
                $this->data[$entity->getId()] = $data;


//                // Recompute change set
//                // (it is only necessary if entity has been change during MESSAGE_PRE_UPDATE event)
//                $em   = $args->getEntityManager();
//                $uow  = $em->getUnitOfWork();
//                $meta = $em->getClassMetadata(get_class($entity));
//                $uow->recomputeSingleEntityChangeSet($meta, $entity);
            };
        }
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if ($entity instanceof Message) {
            $data = array(
                'entity'    => $entity,
                'changeSet' => array()
            );

            foreach ($this->watchedFields as $fieldName) {
                $data['changeSet'][$fieldName] = null;
            }

            $this->data[$entity->getId()] = $data;

//            // Dispatch MESSAGE_POST_PERSIST event.
//            $this->dispatcher->dispatch(
//                MessageEvents::MESSAGE_POST_PERSIST,
//                new MessageChangeEvent($entity, $data['changeSet'])
//            );
        }
    }

//    /**
//     * @param LifecycleEventArgs $args
//     */
//    public function postUpdate(LifecycleEventArgs $args)
//    {
//        $entity = $args->getObject();
//        if ($entity instanceof Message) {
//
//            if (isset($this->data[$entity->getId()])) {
//                // Dispatch MESSAGE_POST_UPDATE event.
//                $this->dispatcher->dispatch(
//                    MessageEvents::MESSAGE_POST_UPDATE,
//                    new MessageChangeEvent($entity, $this->data[$entity->getId()]['changeSet'])
//                );
//            }
//
//        }
//    }

    /**
     * @param PostFlushEventArgs $args
     */
    public function postFlush(PostFlushEventArgs $args)
    {
        $this->handleFlush($args->getEntityManager());
    }


    /**
     * Dispatches MESSAGE_CHANGED event (for each modified or created message entity).
     */
    protected function handleFlush(EntityManager $em)
    {
        $data           = $this->data;
        $this->data     = array();

        $dispatched = false;
        foreach ($data as $dataElem) {

            if ($dataElem['entity']) {
                $this->dispatcher->dispatch(
                    MessageEvents::MESSAGE_CHANGED,
                    new MessageChangeEvent($dataElem['entity'], $dataElem['changeSet'])
                );
            } else {
                $this->dispatcher->dispatch(
                    MessageEvents::MESSAGE_REMOVED,
                    new MessageChangeEvent($dataElem['entity'], $dataElem['changeSet'])
                );
            }
            $dispatched = true;
        }

        if ($dispatched) {
            $em->flush();
        }
    }

}